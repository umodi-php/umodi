<?php

declare(strict_types=1);

namespace Umodi\Assert\Comparison;

use Umodi\AssertResolution;
use Umodi\AssertResult;

function eq(mixed $expected, mixed $actual): AssertResult
{
    $message = sprintf(
        'Expected %s, got %s',
        var_export($expected, true),
        var_export($actual, true),
    );

    return new AssertResult(
        $expected === $actual
            ? AssertResolution::Success
            : AssertResolution::Failed,
        $message,
    );
}
