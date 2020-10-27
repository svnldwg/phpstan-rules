<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure\Inheritance\MutateChildPropertyWithImmutableParent;

/**
 * @immutable
 */
class ImmutableParentWithProperty
{
    protected $immutable;
}
