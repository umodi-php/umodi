<?php

declare(strict_types=1);

namespace Umodi\Assert;

use Umodi\AssertResolution;
use Umodi\AssertResult;

function eq(mixed $expected, mixed $actual): AssertResult
{
    return new AssertResult(
        $expected === $actual
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected %d, got %d', $expected, $actual),
    );
}
