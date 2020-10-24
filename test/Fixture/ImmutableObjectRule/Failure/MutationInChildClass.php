<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure;

/**
 * @immutable
 */
class MutationInChildClass
{
    protected $value;
}