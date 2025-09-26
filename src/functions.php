<?php

declare(strict_types=1);

namespace Umodi;

function unitRegistry(): UnitRegistry
{
    static $registry = null;

    if ($registry === null) {
        $registry = new UnitRegistry();
    }

    return $registry;
}

function _unit(?string $name = null, ?callable $unitCallback = null): UnitRegistry
{
    $registry = unitRegistry();

    if ($name !== null) {
        if ($unitCallback === null) {
            throw new \InvalidArgumentException('Unit callback must not be null when registering a unit.');
        }

        $registry->add($name, $unitCallback);
    }

    return $registry;
}

function unit(string $name, callable $callback): void
{
    unitRegistry()->add($name, $callback);
}

function clearUnits(): void
{
    unitRegistry()->clear();
}
