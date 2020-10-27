<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\MutateChildPropertyWithImmutableParent;

class ChildPropertyMutation extends ImmutableParent
{
    private $childProperty;

    public function setChildProperty()
    {
        $this->childProperty = true;
    }
}
