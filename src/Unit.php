<?php

declare(strict_types=1);

namespace Umodi;

class Unit implements UnitInterface, RunnableUnitInterface
{
    public ?string $skippedReason;
    public ?string $incompleteReason;
    public string $file;
    public int $line;

    private array $tests = [];
    private array $before = [];
    private array $after = [];
    private array $beforeEach = [];
    private array $afterEach = [];

    public function before(callable $callback): void
    {
        $this->before[] = $callback;
    }

    public function after(callable $callback): void
    {
        $this->after[] = $callback;
    }

    public function beforeEach(callable $callback): void
    {
        $this->beforeEach[] = $callback;
    }

    public function afterEach(callable $callback): void
    {
        $this->afterEach[] = $callback;
    }

    public function test(string $name, callable $callback): void
    {
        $this->tests[$name] = $callback;
    }

    public function getTests(): array
    {
        return $this->tests;
    }

    public function runBefore(): void
    {
        if ($this->skippedReason !== null) {
            return;
        }
        foreach ($this->before as $callback) {
            $callback($this);
        }
    }

    public function runAfter(): void
    {
        if ($this->skippedReason !== null) {
            return;
        }
        foreach ($this->after as $callback) {
            $callback($this);
        }
    }

    public function runBeforeEach(): void
    {
        if ($this->skippedReason !== null) {
            return;
        }
        foreach ($this->beforeEach as $callback) {
            $callback($this);
        }
    }

    public function runAfterEach(): void
    {
        if ($this->skippedReason !== null) {
            return;
        }
        foreach ($this->afterEach as $callback) {
            $callback($this);
        }
    }
}
