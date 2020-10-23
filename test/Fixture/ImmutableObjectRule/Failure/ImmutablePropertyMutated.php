<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure;

class ImmutablePropertyMutated
{
    /** @immutable */
    private $value;

    /** @immutable */
    private $mutable;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function mutate()
    {
        $this->mutable = 'mutated';
    }
}
