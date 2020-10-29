<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Helper;

use PhpParser\Node;

class NodeParser
{
    /**
     * @param Node[] $nodes
     *
     * @return Node\Stmt\Class_|null
     */
    public static function getClassNode(array $nodes): ?Node\Stmt\Class_
    {
        foreach ($nodes as $node) {
            if ($node instanceof Node\Stmt\Namespace_ || $node instanceof Node\Stmt\Declare_) {
                $subNodeNames = $node->getSubNodeNames();
                foreach ($subNodeNames as $subNodeName) {
                    $subNode = $node->{$subNodeName};
                    if (!is_array($subNode)) {
                        $subNode = [$subNode];
                    }

                    $result = self::getClassNode($subNode);
                    if ($result) {
                        return $result;
                    }
                }

                continue;
            }

            if ($node instanceof Node\Stmt\Class_) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     *
     * @return Node\Stmt\Property[]
     */
    public static function getClassProperties(Node\Stmt\Class_ $classNode): array
    {
        $properties = [];

        foreach ($classNode->stmts as $property) {
            if ($property instanceof Node\Stmt\Property) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     *
     * @return array<Node\Stmt\Property>
     */
    public static function getNonPrivateProperties(Node\Stmt\Class_ $classNode): array
    {
        $properties = self::getClassProperties($classNode);

        return array_filter($properties, static function (Node\Stmt\Property $property): bool {
            return !$property->isPrivate();
        });
    }
}
