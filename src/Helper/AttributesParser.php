<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Helper;

use PhpParser\Node;

class AttributesParser
{
    /**
     * @param string[] $attributes
     * @param Node[]   $nodes
     *
     * @return bool
     */
    public static function classHasAttribute(array $attributes, array $nodes): bool
    {
        $classNode = NodeParser::getClassNode($nodes);
        if ($classNode === null) {
            return false;
        }

        return self::hasNodeAttribute($classNode, $attributes);
    }

    /**
     * @param string[]    $attributes
     * @param array<Node> $nodes
     *
     * @return array<int, string>
     */
    public static function propertiesWithAttribute(array $attributes, array $nodes): array
    {
        $whitelistedProperties = [];

        $classNode = NodeParser::getClassNode($nodes);
        if ($classNode === null) {
            return $whitelistedProperties;
        }

        $classProperties = NodeParser::getClassProperties($classNode);
        foreach ($classProperties as $property) {
            $whitelisted = self::hasNodeAttribute($property, $attributes);
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
     *
     * @return Node\AttributeGroup[]
     */
    public static function getAttributeGroups(Node $node): array
    {
        if ($node instanceof Node\Stmt\ClassLike || $node instanceof Node\Stmt\Property) {
            echo 'classLike or property' . PHP_EOL;

            return $node->attrGroups;
        }

        return [];
    }

    public static function hasNodeAttribute(Node $node, array $attributes): bool
    {
        $nodeAttributeGroups = self::getAttributeGroups($node);
        echo count($nodeAttributeGroups) . ' attr groups' . PHP_EOL;

        foreach ($nodeAttributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if (in_array($attribute->name->toString(), $attributes)) {
                    return true;
                }
            }
        }

        return false;
    }
}
