<?php

declare(strict_types=1);

namespace Umodi\Assert\Boolean;

use Umodi\AssertResolution;
use Umodi\AssertResult;

function isTrue(mixed $actual): AssertResult
{
    return new AssertResult(
        $actual === true
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected `true`, actual %s', var_export($actual, true)),
    );
}
