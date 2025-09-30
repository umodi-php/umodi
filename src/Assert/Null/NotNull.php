<?php

declare(strict_types=1);

namespace Umodi\Assert\Null;

use Umodi\Result\AssertResult;
use Umodi\Severity\AssertResolution;

function notNull(mixed $actual): AssertResult
{
    return new AssertResult(
        $actual !== null
            ? AssertResolution::Success
            : AssertResolution::Failed,
        'Did not expect `null` value',
    );
}
