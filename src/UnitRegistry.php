<?php

declare(strict_types=1);

namespace Umodi;

final class UnitRegistry
{
    /**
     * @var array<string, callable>
     */
    private array $units = [];

    public function add(string $name, callable $unitCallback): void
    {
        $this->units[$name] = $unitCallback;
    }

    /**
     * @return array<string, callable>
     */
    public function all(): array
    {
        return $this->units;
    }

    public function clear(): void
    {
        $this->units = [];
    }
}
