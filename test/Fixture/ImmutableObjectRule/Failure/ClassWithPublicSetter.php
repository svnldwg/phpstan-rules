<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Fixture\ImmutableObjectRule\Failure;

/**
 * @immutable
 */
class ClassWithPublicSetter
{
    private $value;

    public function setValue($value)
    {
        $this->value = $value;
    }
}
