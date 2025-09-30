<?php

declare(strict_types=1);

namespace Umodi\Assert\Array;

use Umodi\Result\AssertResult;
use Umodi\Severity\AssertResolution;

/**
 * @param iterable<mixed>|string $haystack
 */
function contains(mixed $needle, iterable|string $haystack): AssertResult
{
    if (is_string($haystack)) {
        $success = str_contains($haystack, (string) $needle);
        $message = sprintf(
            'Expected string %s to contain %s',
            var_export($haystack, true),
            var_export($needle, true),
        );

        return new AssertResult(
            $success ? AssertResolution::Success : AssertResolution::Failed,
            $message,
        );
    }

    $success = false;

    foreach ($haystack as $value) {
        if ($value === $needle) {
            $success = true;
            break;
        }
    }

    $message = sprintf(
        'Expected collection to contain %s',
        var_export($needle, true),
    );

    return new AssertResult(
        $success ? AssertResolution::Success : AssertResolution::Failed,
        $message,
    );
}
