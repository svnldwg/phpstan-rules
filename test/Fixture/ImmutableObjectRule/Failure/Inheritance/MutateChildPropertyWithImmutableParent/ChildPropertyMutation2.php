<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\MutateChildPropertyWithImmutableParent;

class ChildPropertyMutation2 extends ImmutableParentWithProperty
{
    private $immu;

    public function set()
    {
        $this->immu = 'mutated';
    }
}
