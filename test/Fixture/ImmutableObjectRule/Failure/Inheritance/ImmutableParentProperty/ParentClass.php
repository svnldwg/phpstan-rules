<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\ImmutableParentProperty;

class ParentClass
{
    /** @psalm-immutable */
    protected $foo;
}
