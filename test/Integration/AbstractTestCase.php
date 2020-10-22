<?php

declare(strict_types=1);

namespace Svnldwg\PHPStan\Test\Integration;

use PHPStan\Testing\RuleTestCase;

abstract class AbstractTestCase extends RuleTestCase
{
    /**
     * @dataProvider provideCasesWhereAnalysisShouldSucceed
     *
     * @param string $path
     */
    final public function testAnalysisSucceeds(string $path): void
    {
        $this->analyse(
            [
                $path,
            ],
            []
        );
    }

    /**
     * @dataProvider provideCasesWhereAnalysisShouldFail
     *
     * @param string $path
     * @param array  $error
     */
    final public function testAnalysisFails(string $path, array $error): void
    {
        $this->analyse(
            [
                $path,
            ],
            [
                $error,
            ]
        );
    }

    abstract public function provideCasesWhereAnalysisShouldSucceed(): iterable;

    abstract public function provideCasesWhereAnalysisShouldFail(): iterable;
}
