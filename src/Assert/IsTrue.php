<?php

declare(strict_types=1);

namespace Umodi\Assert;

use Umodi\AssertResolution;
use Umodi\AssertResult;

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
