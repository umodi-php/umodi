<?php

declare(strict_types=1);

namespace Umodi\Assert\Type;

use Umodi\AssertResolution;
use Umodi\AssertResult;

/**
 * @param class-string $expected
 */
function isInstanceOf(string $expected, object $actual): AssertResult
{
    $success = \is_a($actual, $expected);

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        sprintf(
            'Expected instance of %s, got %s',
            $expected,
            $actual::class,
        ),
    );
}
