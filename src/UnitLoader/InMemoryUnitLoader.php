<?php

declare(strict_types=1);

namespace Umodi\UnitLoader;

final class InMemoryUnitLoader implements UnitLoaderInterface {
    /** @param array<string, callable> $defs */
    public function __construct(private array $defs) {}
    public function load(): iterable { return $this->defs; }
}
