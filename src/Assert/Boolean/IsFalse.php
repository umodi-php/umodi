<?php

declare(strict_types=1);

namespace Umodi\Assert\Boolean;

use Umodi\AssertResult;
use Umodi\Severity\AssertResolution;

function isFalse(mixed $actual): AssertResult
{
    return new AssertResult(
        $actual === false
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected `false`, actual %s', var_export($actual, true)),
    );
}
