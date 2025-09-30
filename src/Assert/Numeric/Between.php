<?php

declare(strict_types=1);

namespace Umodi\Assert\Numeric;

use Umodi\Result\AssertResult;
use Umodi\Severity\AssertResolution;

/**
 * @param int|float $min
 * @param int|float $max
 * @param int|float $actual
 */
function between(int|float $min, int|float $max, int|float $actual, bool $inclusive = true): AssertResult
{
    $success = $inclusive
        ? ($actual >= $min && $actual <= $max)
        : ($actual > $min && $actual < $max);

    $message = sprintf(
        'Expected %s to be %s %s and %s',
        var_export($actual, true),
        $inclusive ? 'between' : 'strictly between',
        var_export($min, true),
        var_export($max, true),
    );

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        $message,
    );
}
