<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Helper;

use PhpParser\Node;

class Converter
{
    /**
     * @param array<Node\Stmt\Property> $properties
     *
     * @return string[]
     */
    public static function propertyStringNames(array $properties)
    {
        return array_map(static function (Node\Stmt\Property $property): string {
            return (string)reset($property->props)->name;
        }, $properties);
    }
}
