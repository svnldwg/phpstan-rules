<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Helper;

class BackwardsIterator
{
    /**
     * @param mixed[] $array
     *
     * @return \Generator<mixed>
     */
    public static function iterateBackwards(array $array): \Generator
    {
        for (end($array); key($array) !== null; prev($array)) {
            yield current($array);
        }
    }
}
