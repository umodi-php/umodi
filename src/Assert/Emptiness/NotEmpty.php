<?php

declare(strict_types=1);

namespace Umodi\Assert\Emptiness;

use Countable;
use Umodi\AssertResolution;
use Umodi\AssertResult;

/**
 * @param Countable|array|string $actual
 */
function notEmpty(Countable|array|string $actual): AssertResult
{
    if (is_string($actual)) {
        $success = $actual !== '';
        $message = 'Expected string not to be empty';

        return new AssertResult(
            $success ? AssertResolution::Success : AssertResolution::Failed,
            $message,
        );
    }

    $count = \count($actual);
    $success = $count > 0;

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        'Expected value not to be empty',
    );
}
