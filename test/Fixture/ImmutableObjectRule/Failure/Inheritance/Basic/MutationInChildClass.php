<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\Basic;

/**
 * @immutable
 */
class MutationInChildClass
{
    protected $value;
}
