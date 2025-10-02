<?php

declare(strict_types=1);

namespace Umodi\ProgressWatcher;

use Umodi\Result\TestOutcome;
use Umodi\Unit;

final class ProgressWatcherSpy implements ProgressWatcherInterface
{
    /** @var array<int, array{0:string,1:mixed}> */
    public array $events = [];
    public mixed $onStartTriggeredEvents = [];
    public array $onEndTriggeredEvents = [];
    public array $outcomes = [];
    public array $onUnitStartTriggeredEvents = [];

    public function onStart(array $units): void
    {
        $this->onStartTriggeredEvents[] = array_keys($units);
    }

    public function onUnitStart(string $unitTitle, Unit $unit): void
    {
        $this->onUnitStartTriggeredEvents[] = $unitTitle;
    }

    public function onTestResult(TestOutcome $outcome): void
    {
        $this->outcomes[] = $outcome;
    }

    public function onEnd(): void
    {
        $this->onEndTriggeredEvents[] = null;
    }
}
