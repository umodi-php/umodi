<?php

declare(strict_types=1);

namespace Umodi\Assert\Null;

use Umodi\AssertResolution;
use Umodi\AssertResult;

function isNull(mixed $actual): AssertResult
{
    return new AssertResult(
        $actual === null
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected `null`, actual %s', var_export($actual, true)),
    );
}
