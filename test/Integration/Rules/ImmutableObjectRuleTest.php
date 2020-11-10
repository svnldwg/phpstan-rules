<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Integration\Rules;

use PhpParser\Lexer;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7;
use PHPStan\Parser\SimpleParser;
use PHPStan\Rules\Rule;
use Svnldwg\PHPStan\Rules\ImmutableObjectRule;
use Svnldwg\PHPStan\Test\Integration\AbstractTestCase;

/**
 * @internal
 *
 * @covers \Svnldwg\PHPStan\Rules\ImmutableObjectRule
 */
final class ImmutableObjectRuleTest extends AbstractTestCase
{
    /**
     * @return iterable<string,string[]>
     */
    public function provideCasesWhereAnalysisShouldSucceed(): iterable
    {
        $paths = [
            'immutable-class'    => __DIR__ . '/../../Fixture/ImmutableObjectRule/Success/ImmutableClass.php',
            'immutable-property' => __DIR__ . '/../../Fixture/ImmutableObjectRule/Success/ImmutableProperty.php',
            'not-annotated'      => __DIR__ . '/../../Fixture/ImmutableObjectRule/Success/NotAnnotated.php',
        ];

        foreach ($paths as $description => $path) {
            yield $description => [
                $path,
            ];
        }
    }

    /**
     * @return iterable<string,array>
     */
    public function provideCasesWhereAnalysisShouldFail(): iterable
    {
        $paths = [
            'class-with-public-setter' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/ClassWithPublicSetter.php',
                [
                    'Class is declared immutable, but class property "value" is modified in method "setValue"',
                    16,
                ],
            ],
            'immutable-property-mutated' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/ImmutablePropertyMutated.php',
                [
                    'Property is declared immutable, but class property "mutable" is modified in method "mutate"',
                    22,
                ],
            ],
            'mutation-in-child-class' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Inheritance/Basic/MutationInChildClassChild.php',
                [
                    'Property is declared immutable, but class property "value" is modified in method "setValue"',
                    11,
                ],
            ],
            'deep-inheritance' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Inheritance/Deep/ChildClass.php',
                [
                    'Property is declared immutable, but class property "parentValue" is modified in method "mutate"',
                    11,
                ],
            ],
            'mutate-child-property-with-immutable-parent' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Inheritance/MutateChildPropertyWithImmutableParent/ChildPropertyMutation.php',
                [
                    'Class is declared immutable, but class property "childProperty" is modified in method "setChildProperty"',
                    13,
                ],
            ],
            'mutate-immutable-parent-property' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Inheritance/MutateChildPropertyWithImmutableParent/ChildPropertyMutation2.php',
                [
                    'Property is declared immutable, but class property "immu" is modified in method "set"',
                    13,
                ],
            ],
            'public-immutable-class-property' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/PublicImmutableProperty/PublicImmutableClassProperty.php',
                [
                    'Property "shouldNotBePublic" is declared immutable, but is public and therefore mutable',
                    12,
                ],
            ],
            'public-immutable-property' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/PublicImmutableProperty/PublicImmutableProperty.php',
                [
                    'Property "shouldNotBePublic" is declared immutable, but is public and therefore mutable',
                    10,
                ],
            ],
            'public-immutable-property-inheritance' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/PublicImmutableProperty/Inheritance.php',
                [
                    'Property "shouldNotBePublicBecauseParentIsImmutable" is declared immutable, but is public and therefore mutable',
                    9,
                ],
            ],
            'immutable-parent-property' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Inheritance/ImmutableParentProperty/ChildClass.php',
                [
                    'Property is declared immutable, but class property "foo" is modified in method "set"',
                    11,
                ],
            ],
            'class-with-immutable-attribute' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/Attributes/ClassWithImmutableAttribute.php',
                [
                    'Property is declared immutable, but class property "foo" is modified in method "set"',
                    11,
                ],
            ],
        ];

        foreach ($paths as $description => [$path, $error]) {
            yield $description => [
                $path,
                $error,
            ];
        }
    }

    /**
     * @return Rule<\PhpParser\Node>
     */
    protected function getRule(): Rule
    {
        $lexer = new Lexer();

        return new ImmutableObjectRule(
            new SimpleParser(
                new Php7($lexer),
                new NameResolver()
            )
        );
    }
}
