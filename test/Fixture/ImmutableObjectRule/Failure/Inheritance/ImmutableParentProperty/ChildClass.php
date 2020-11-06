<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\ImmutableParentProperty;

class ChildClass extends ParentClass
{
    public function set(): void
    {
        $this->foo = 10; // declared immutable in parent class
    }
}
