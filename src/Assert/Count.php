<?php

declare(strict_types=1);

namespace Umodi\Assert;

use Countable;
use Umodi\AssertResolution;
use Umodi\AssertResult;

function count(int $expected, Countable|array $actual): AssertResult
{
    return new AssertResult(
        $expected === \count($actual)
            ? AssertResolution::Success
            : AssertResolution::Failed,
        sprintf('Expected %d items, got %d', \count($actual), $expected)
    );
}
