<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\PublicImmutableProperty;

class PublicImmutableProperty
{
    /** @psalm-immutable */
    public $shouldNotBePublic;
}
