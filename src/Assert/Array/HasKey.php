<?php

declare(strict_types=1);

namespace Umodi\Assert\Array;

use ArrayAccess;
use Umodi\AssertResult;
use Umodi\Severity\AssertResolution;

/**
 * @param array<array-key, mixed>|ArrayAccess<array-key, mixed> $actual
 */
function hasKey(int|string $key, array|ArrayAccess $actual): AssertResult
{
    $exists = is_array($actual)
        ? array_key_exists($key, $actual)
        : $actual->offsetExists($key);

    return new AssertResult(
        $exists ? AssertResolution::Success : AssertResolution::Failed,
        sprintf('Expected key %s to exist', var_export($key, true)),
    );
}
