<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Svnldwg\PHPStan\Helper\AnnotationParser;
use Svnldwg\PHPStan\Helper\BackwardsIterator;
use Svnldwg\PHPStan\Helper\Converter;
use Svnldwg\PHPStan\Helper\NodeParser;

/**
 * @template-implements Rule<Node>
 */
class ImmutableObjectRule implements Rule
{
    private const WHITELISTED_ANNOTATIONS = [
        'psalm-immutable',
        'immutable',
    ];

    /** @var \PHPStan\Parser\Parser */
    private $parser;

    /** @var string */
    private $currentClass = '';
    /** @var string[] */
    private $immutableProperties = [];
    /** @var bool */
    private $isImmutable = false;

    public function __construct(\PHPStan\Parser\Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Expr\AssignOp
            && !$node instanceof Assign
            && !$node instanceof Node\Stmt\Property
        ) {
            return [];
        }

        if (!$scope->isInClass()) {
            return [];
        }

        $this->detectImmutableProperties($scope);

        $isImmutable = $this->isImmutable;
        $immutableProperties = $this->immutableProperties;
        if (!$isImmutable && empty($immutableProperties)) {
            return [];
        }

        if ($node instanceof Node\Stmt\Property) {
            return $this->assertImmutablePropertyIsNotPublic($node);
        }

        while ($node->var instanceof Node\Expr\ArrayDimFetch) {
            $node = $node->var;
        }

        if (!$isImmutable
            && !empty($immutableProperties)
            && property_exists($node->var, 'name')
            && !in_array((string)$node->var->name, $immutableProperties)
        ) {
            return [];
        }

        if (!$node->var instanceof Node\Expr\PropertyFetch
            && !$node->var instanceof Node\Expr\StaticPropertyFetch
        ) {
            return [];
        }

        if ($scope->getFunctionName() === '__construct') {
            return [];
        }

        $propertyName = $node->var->name;
        if ($propertyName instanceof Node\Identifier) {
            $propertyName = (string)$propertyName;
        }
        if (!is_string($propertyName)) {
            $propertyName = '';
        }

        if ($scope->getFunction() instanceof ClassMemberReflection && $scope->getFunction()->isPrivate()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '%s is declared immutable, but class property "%s" is modified in private method "%s" which could be called from outside the constructor',
                    empty($immutableProperties) ? 'Class' : 'Property',
                    $propertyName,
                    $scope->getFunctionName()
                ))->build(),
            ];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s is declared immutable, but class property "%s" is modified in method "%s"',
                empty($immutableProperties) ? 'Class' : 'Property',
                $propertyName,
                $scope->getFunctionName()
            ))->build(),
        ];
    }

    /**
     * @param Scope $scope
     *
     * @return array{properties: string[], hasImmutableParent: bool}
     */
    private function getInheritedImmutableProperties(Scope $scope): array
    {
        if ($scope->getClassReflection() === null) {
            return ['properties' => [], 'hasImmutableParent' => false];
        }

        $immutableParentProperties = [];

        $parents = $scope->getClassReflection()->getParents();
        $parentsTopDown = BackwardsIterator::iterateBackwards($parents);
        $hasImmutableParent = false;
        foreach ($parentsTopDown as $parent) {
            $fileName = $parent->getFileName();
            if (!$fileName) {
                continue;
            }

            $nodes = $this->parser->parseFile($fileName);
            $classNode = NodeParser::getClassNode($nodes);
            if (!$classNode) {
                continue;
            }

            if ($hasImmutableParent || AnnotationParser::classHasAnnotation(self::WHITELISTED_ANNOTATIONS, $nodes)) {
                $hasImmutableParent = true;

                $immutableParentProperties += Converter::propertyStringNames(NodeParser::getNonPrivateProperties($classNode));
            }

            // @TODO: detect non private parent properties annotated as immutable (instead of whole class)
        }

        return ['properties' => $immutableParentProperties, 'hasImmutableParent' => $hasImmutableParent];
    }

    /**
     * @param Scope $scope
     */
    private function detectImmutableProperties(Scope $scope): void
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            $this->isImmutable = false;
            $this->immutableProperties = [];
            $this->currentClass = '';

            return;
        }
        $currentClassName = $classReflection->getName();
        if ($this->currentClass === $currentClassName) {
            return;
        }

        ['properties' => $immutableProperties, 'hasImmutableParent' => $hasImmutableParent] = $this->getInheritedImmutableProperties($scope);

        $nodes = $this->parser->parseFile($scope->getFile());
        $isImmutable = $hasImmutableParent || AnnotationParser::classHasAnnotation(self::WHITELISTED_ANNOTATIONS, $nodes);

        if (!empty($immutableProperties)) {
            $classNode = NodeParser::getClassNode($nodes);
            if ($classNode !== null) {
                $classProperties = NodeParser::getClassProperties($classNode);
                $classPropertyNames = Converter::propertyStringNames($classProperties);
                $immutableProperties = array_merge($immutableProperties, $classPropertyNames);
            }
        }

        if (empty($immutableProperties)) {
            $immutableProperties = AnnotationParser::propertiesWithWhitelistedAnnotations(self::WHITELISTED_ANNOTATIONS, $nodes);
        }

        $this->immutableProperties = $immutableProperties;
        $this->isImmutable = $isImmutable;
        $this->currentClass = $classReflection->getName();
    }

    /**
     * @param Node\Stmt\Property $property
     *
     * @throws \PHPStan\ShouldNotHappenException
     *
     * @return RuleError[]
     */
    private function assertImmutablePropertyIsNotPublic(Node\Stmt\Property $property): array
    {
        $propertyName = Converter::propertyToString($property);
        $propertyIsImmutable = $this->isImmutable || in_array($propertyName, $this->immutableProperties);

        if ($propertyIsImmutable && $property->isPublic()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Property "%s" is declared immutable, but is public and therefore mutable',
                    $propertyName
                ))->build(),
            ];
        }

        return [];
    }
}
