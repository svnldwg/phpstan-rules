<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Svnldwg\PHPStan\Helper\AnnotationParser;
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
        if (!$node instanceof Node\Expr\AssignOp && !$node instanceof Assign) {
            return [];
        }

        if (!$scope->isInClass()) {
            return [];
        }

        $immutableProperties = $this->getParentClasses($scope);

        $nodes = $this->parser->parseFile($scope->getFile());

        if (!AnnotationParser::classHasAnnotation(self::WHITELISTED_ANNOTATIONS, $nodes)) {
            $immutableProperties = array_merge(
                $immutableProperties,
                AnnotationParser::propertiesWithWhitelistedAnnotations(self::WHITELISTED_ANNOTATIONS, $nodes)
            );

            if (empty($immutableProperties)) {
                return [];
            }
        }

        while ($node->var instanceof Node\Expr\ArrayDimFetch) {
            $node = $node->var;
        }

        if (!empty($immutableProperties)
            && property_exists($node->var, 'name')
            && !in_array((string)$node->var->name, $immutableProperties)
        ) {
            return [];
        }

        if (
            !$node->var instanceof Node\Expr\PropertyFetch
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

    private function getParentClasses(Scope $scope): array
    {
        if ($scope->getClassReflection() === null) {
            return [];
        }

        $immutableParentProperties = [];

        // TODO: consider multiple layers of inheritance (parent 1 is not declared immutable, but parent 2 is, so properties of parent 1 need to inherit immutability

        foreach ($scope->getClassReflection()->getParents() as $parent) {
            $fileName = $parent->getFileName();
            if (!$fileName) {
                continue;
            }

            $nodes = $this->parser->parseFile($fileName);
            $classNode = NodeParser::getClassNode($nodes);
            if (!$classNode) {
                continue;
            }

            if (AnnotationParser::classHasAnnotation(self::WHITELISTED_ANNOTATIONS, $nodes)) {
                $immutableParentProperties += array_map(static function (Node\Stmt\Property $property): string {
                    return (string)reset($property->props)->name;
                }, NodeParser::getNonPrivateProperties($classNode));
            }

            // @TODO: detect non private parent properties annotated as immutable
        }

        return $immutableParentProperties;
    }
}
