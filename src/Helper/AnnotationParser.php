<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Helper;

use PhpParser\Comment\Doc;
use PhpParser\Node;

class AnnotationParser
{
    /**
     * @param string[] $annotations
     * @param Node[]   $nodes
     *
     * @return bool
     */
    public static function classHasAnnotation(array $annotations, array $nodes): bool
    {
        $classNode = NodeParser::getClassNode($nodes);
        if ($classNode === null) {
            return false;
        }

        return self::isWhitelisted($classNode, $annotations);
    }

    /**
     * @param string[]    $annotations
     * @param array<Node> $nodes
     *
     * @return array<int, string>
     */
    public static function propertiesWithWhitelistedAnnotations(array $annotations, array $nodes): array
    {
        $whitelistedProperties = [];

        $classNode = NodeParser::getClassNode($nodes);
        if ($classNode === null) {
            return $whitelistedProperties;
        }

        foreach ($classNode->stmts as $property) {
            if (!$property instanceof Node\Stmt\Property) {
                continue;
            }

            $whitelisted = self::isWhitelisted($property, $annotations);
            if ($whitelisted) {
                foreach ($property->props as $prop) {
                    $whitelistedProperties[] = (string)$prop->name;
                }
            }
        }

        return $whitelistedProperties;
    }

    /**
     * @param Node $node
     * @return string[]
     */
    public static function getAnnotations(Node $node): array
    {
        $docComment = $node->getDocComment();

        if (!$docComment instanceof Doc) {
            return [];
        }

        if (is_int(preg_match_all('/@(\S+)(?=\s|$)/', $docComment->getReformattedText(), $matches))) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param Node     $node
     * @param string[] $whitelistAnnotations
     *
     * @return bool
     */
    private static function isWhitelisted(Node $node, array $whitelistAnnotations): bool
    {
        $nodeAnnotations = self::getAnnotations($node);

        foreach ($nodeAnnotations as $annotation) {
            foreach ($whitelistAnnotations as $whitelistedAnnotation) {
                if (0 === mb_strpos($annotation, $whitelistedAnnotation)) {
                    return true;
                }
            }
        }

        return false;
    }
}
