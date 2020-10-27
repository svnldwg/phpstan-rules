<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\Deep;

class ParentClass extends GrandParentClass
{
    protected $parentValue;
}
