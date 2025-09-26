<?php

declare(strict_types=1);

namespace Umodi;

function _unit(string $name = null, callable $unitCallback = null)
{
    static $units = [];

    if ($name === null) {
        return $units;
    }

    $units[$name] = $unitCallback;

    return $units;
}

function unit(string $name, callable $callback): void
{
    _unit($name, $callback);
}
