<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Integration\Rules;

use PhpParser\Lexer;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser\Php7;
use PHPStan\NodeVisitor\StatementOrderVisitor;
use PHPStan\Parser\DirectParser;
use PHPStan\Parser\NodeChildrenVisitor;
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
            /*'mutation-in-child-class' => [
                __DIR__ . '/../../Fixture/ImmutableObjectRule/Failure/MutationInChildClass.php',
                [
                    'Class is declared immutable, but class property "value" is modified in method "setValue"',
                    26,
                ],
            ],*/
        ];

        foreach ($paths as $description => [$path, $error]) {
            yield $description => [
                $path,
                $error,
            ];
        }
    }

    protected function getRule(): Rule
    {
        $lexer = new Lexer();

        return new ImmutableObjectRule(
            new DirectParser(
                new Php7($lexer),
                $lexer,
                new NameResolver(),
                new NodeConnectingVisitor(),
                new StatementOrderVisitor(),
                new NodeChildrenVisitor()
            )
        );
    }
}
