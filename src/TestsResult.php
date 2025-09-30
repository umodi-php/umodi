<?php

declare(strict_types=1);

namespace Umodi;

use Umodi\Result\TestOutcome;
use Umodi\Severity\AssertResolution;

final class TestsResult
{
    /** @var TestOutcome[] */
    private array $outcomes = [];
    public function addOutcome(TestOutcome $o): void { $this->outcomes[] = $o; }
    /** @return TestOutcome[] */
    public function outcomes(): array { return $this->outcomes; }

    public int $tests = 0;
    public int $assertions = 0;

    /** @var array<string, int> */
    private array $testsByResolution = [];
    private AssertResolution $worstResolution;

    public function __construct()
    {
        foreach (AssertResolution::cases() as $resolution) {
            $this->testsByResolution[$resolution->value] = 0;
        }

        $this->worstResolution = AssertResolution::Success;
    }

    public function registerTestResult(AssertResolution $resolution, int $assertionsCount): void
    {
        $this->tests++;
        $this->assertions += $assertionsCount;
        $this->testsByResolution[$resolution->value] ??= 0;
        $this->testsByResolution[$resolution->value]++;

        if ($this->severity($resolution) > $this->severity($this->worstResolution)) {
            $this->worstResolution = $resolution;
        }
    }

    public function testsFor(AssertResolution $resolution): int
    {
        return $this->testsByResolution[$resolution->value] ?? 0;
    }

    public function hasFailures(): bool
    {
        return $this->testsFor(AssertResolution::Failed) > 0
            || $this->testsFor(AssertResolution::Error) > 0;
    }

    public function exitCode(): int
    {
        return $this->hasFailures() ? 1 : 0;
    }

    private function severity(AssertResolution $resolution): int
    {
        return match ($resolution) {
            AssertResolution::Success => 0,
            AssertResolution::Skipped => 1,
            AssertResolution::Incomplete => 2,
            AssertResolution::Warning => 3,
            AssertResolution::Failed => 4,
            AssertResolution::Error => 5,
            AssertResolution::Risky => 6,
        };
    }
}
