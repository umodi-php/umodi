<?php

declare(strict_types=1);

namespace Umodi\Assert\Array;

use Countable;
use Umodi\AssertResolution;
use Umodi\AssertResult;

function count(int $expected, Countable|array $actual): AssertResult
{
    $actualCount = \count($actual);

    return new AssertResult(
        $expected === $actualCount
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected %d items, got %d', $expected, $actualCount)
    );
}
