<?php

declare(strict_types=1);

namespace umodi\src\Unit\Assert;

use umodi\src\Unit\AssertResolution;
use umodi\src\Unit\AssertResult;

class Eq implements AssertInterface
{
    public static function a(mixed $expected, mixed $actual): AssertResult
    {
        return new AssertResult(
            $expected === $actual
                ? AssertResolution::Success
                : AssertResolution::Failed,
            '',
        );
    }
}
