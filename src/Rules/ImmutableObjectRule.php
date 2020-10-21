<?php


namespace Svnldwg\PHPStan\Rules;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class ImmutableObjectRule implements Rule
{
    private const WHITELISTED_ANNOTATIONS = [
        'psalm-immutable',
        'immutable',
    ];

    /**
     * @var \PHPStan\Parser\Parser
     */
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
        
        $nodes = $this->parser->parseFile($scope->getFile());
        
        if (!$this->classHasWhitelistedAnnotation($nodes)) {
            return [];
        }
        
        while ($node->var instanceof Node\Expr\ArrayDimFetch) {
            $node = $node->var;
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
        
        if ($scope->getFunction() && $scope->getFunction()->isPrivate()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Class is declared immutable, but class property "%s" is modified in private method "%s" which could be called from outside the constructor',
                    $node->var->name,
                    $scope->getFunctionName()
                ))->build()
            ];
        }
                        
        return [
            RuleErrorBuilder::message(sprintf(
                'Class is declared immutable, but class property "%s" is modified in method "%s"',
                $node->var->name,
                $scope->getFunctionName()
            ))->build()
        ];
    }

    /**
     * @param Node[] $nodes
     *
     * @return bool
     */
    private function classHasWhitelistedAnnotation(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof Node\Stmt\Namespace_ || $node instanceof Node\Stmt\Declare_) {
                $subNodeNames = $node->getSubNodeNames();
                foreach ($subNodeNames as $subNodeName) {
                    $subNode = $node->{$subNodeName};
                    if (!is_array($subNode)) {
                        $subNode = [$subNode];
                    }
                    
                    $result = $this->classHasWhitelistedAnnotation($subNode);
                    if ($result) {
                        return true;
                    }
                }
            }
            
            if ($node instanceof Node\Stmt\Class_) {
                $whitelisted = $this->isWhitelisted($node);
                if ($whitelisted) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function isWhitelisted(Node $node): bool
    {
        $docComment = $node->getDocComment();
        
        if (!$docComment instanceof Doc) {
            return false;
        }

        if (is_int(preg_match_all('/@(\S+)(?=\s|$)/', $docComment->getReformattedText(), $matches))) {
            foreach ($matches[1] as $annotation) {
                foreach (self::WHITELISTED_ANNOTATIONS as $whitelistedAnnotation) {
                    if (0 === mb_strpos($annotation, $whitelistedAnnotation)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}