<?php

declare(strict_types=1);

namespace Umodi\ProgressWatcher;

use Umodi\Unit;
use Unit\AssertCollector;

interface ProgressWatcherInterface
{
    /**
     * @param Array<string, Unit> $units
     * @return void
     */
    public function onStart(array $units): void;
    public function onUnitStart(string $unitTitle, Unit $unit): void;
    public function onTestResult(string $unitTitle, Unit $unit, string $testTitle, AssertCollector $assertCollector);
    public function onEnd(): void;
}
