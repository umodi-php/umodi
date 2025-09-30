<?php

declare(strict_types=1);

namespace Umodi\Assert\Numeric;

use Umodi\Result\AssertResult;
use Umodi\Severity\AssertResolution;

/**
 * @param int|float $expectedUpperBound
 * @param int|float $actual
 */
function lessThan(int|float $expectedUpperBound, int|float $actual): AssertResult
{
    $success = $actual < $expectedUpperBound;

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        sprintf(
            'Expected %s to be less than %s',
            var_export($actual, true),
            var_export($expectedUpperBound, true),
        ),
    );
}
