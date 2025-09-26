<?php

declare(strict_types=1);

namespace umodi\src\Unit\Assert;

use Countable;

class Count implements AssertInterface
{
    public static function a(int $expected, Countable|array $actual): AssertInterface
    {

    }
}
