<?php

declare(strict_types=1);

namespace Umodi\ProgressWatcher;

use Umodi\Result\TestOutcome;
use Umodi\Unit;

interface ProgressWatcherInterface
{
    /**
     * @param Array<string, Unit> $units
     * @return void
     */
    public function onStart(array $units): void;
    public function onUnitStart(string $unitTitle, Unit $unit): void;
    public function onEnd(): void;

    public function onTestResult(TestOutcome $outcome);
}
