<?php

declare(strict_types=1);

namespace Umodi\Assert\Null;

use Umodi\AssertResult;
use Umodi\Severity\AssertResolution;

function isNull(mixed $actual): AssertResult
{
    return new AssertResult(
        $actual === null
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected `null`, actual %s', var_export($actual, true)),
    );
}
