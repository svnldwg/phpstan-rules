<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Success;

/**
 * @immutable
 */
class ImmutableClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
