<?php

declare(strict_types=1);

namespace Umodi\Assert\Emptiness;

use Countable;
use Umodi\AssertResult;
use Umodi\Severity\AssertResolution;

/**
 * @param Countable|array|string $actual
 */
function isEmpty(Countable|array|string $actual): AssertResult
{
    if (is_string($actual)) {
        $success = $actual === '';
        $message = sprintf('Expected empty string, got %s', var_export($actual, true));

        return new AssertResult(
            $success ? AssertResolution::Success : AssertResolution::Failed,
            $message,
        );
    }

    $count = \count($actual);
    $success = $count === 0;

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        'Expected value to be empty',
    );
}
