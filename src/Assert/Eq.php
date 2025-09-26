<?php

declare(strict_types=1);

namespace Umodi\Assert;

use Umodi\AssertResolution;
use Umodi\AssertResult;

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
