<?php

declare(strict_types=1);

namespace Umodi\Assert\Numeric;

use Umodi\AssertResolution;
use Umodi\AssertResult;

/**
 * @param int|float $expectedLowerBound
 * @param int|float $actual
 */
function greaterThan(int|float $expectedLowerBound, int|float $actual): AssertResult
{
    $success = $actual > $expectedLowerBound;

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        sprintf(
            'Expected %s to be greater than %s',
            var_export($actual, true),
            var_export($expectedLowerBound, true),
        ),
    );
}
