<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\Basic;

class MutationInChildClassChild extends MutationInChildClass
{
    public function setValue($value)
    {
        $this->value = $value;
    }
}
