<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Success;

class ImmutableProperty
{
    public $mutablesCanBePublic;

    /** @immutable */
    private $value;

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
