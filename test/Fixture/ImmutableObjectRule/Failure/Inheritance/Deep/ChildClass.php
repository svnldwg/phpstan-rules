<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\Deep;

class ChildClass extends ParentClass
{
    public function mutate(): void
    {
        $this->parentValue = 10;
    }
}
