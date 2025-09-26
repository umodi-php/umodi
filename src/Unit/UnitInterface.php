<?php

declare(strict_types=1);

namespace umodi\src\Unit;

interface UnitInterface
{
    public function before(callable $callback): void;
    public function after(callable $callback): void;
    public function beforeEach(callable $callback): void;
    public function afterEach(callable $callback): void;

    public function test(string $name, callable $callback): void;
}
