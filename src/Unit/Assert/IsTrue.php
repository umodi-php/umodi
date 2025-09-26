<?php

declare(strict_types=1);

namespace umodi\src\Unit\Assert;

use umodi\src\Unit\AssertResolution;
use umodi\src\Unit\AssertResult;

class IsTrue implements AssertInterface
{
    public static function a(mixed $actual): AssertResult
    {
        return new AssertResult(
            $actual === true
                ? AssertResolution::Success
                : AssertResolution::Failed,
            sprintf('Expected `true`, actual `%s`', $actual),
        );
    }
}
