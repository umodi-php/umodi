<?php

declare(strict_types=1);

namespace Umodi\ProgressWatcher;

use Umodi\Result\Assertion;
use Umodi\Result\TestOutcome;
use Umodi\Severity\AssertResolution;
use Umodi\Unit;

class CliProgressWatcher implements ProgressWatcherInterface
{
    private int $testsCount = 0;
    private int $testsProcessed = 0;
    private string $lineBuffer = '';

    /** @var array<string, array<string, array{resolution: AssertResolution, assertions: Assertion[] }>> */
    private array $results = []; // [unitTitle][testTitle] => {resolution, assertions}

    /** @var array<AssertResolution::*, int> */
    private array $testTotals = [
        AssertResolution::Success->value => 0,
        AssertResolution::Failed->value => 0,
        AssertResolution::Error->value => 0,
        AssertResolution::Warning->value => 0,
        AssertResolution::Incomplete->value => 0,
        AssertResolution::Skipped->value => 0,
    ];

    /** @var array<AssertResolution::*, int> */
    private array $assertionTotals = [
        AssertResolution::Success->value => 0,
        AssertResolution::Failed->value => 0,
        AssertResolution::Error->value => 0,
        AssertResolution::Warning->value => 0,
        AssertResolution::Incomplete->value => 0,
        AssertResolution::Skipped->value => 0,
    ];

    public function __construct()
    {
    }

    public function onStart(array $units): void
    {
        foreach ($units as $unit) {
            $this->testsCount += count($unit->getTests());
        }
    }

    public function onUnitStart(string $unitTitle, Unit $unit): void
    {
    }

    public function onTestResult(TestOutcome $outcome): void
    {
        $this->testsProcessed++;

        $testResolution = $outcome->resolution;

        $this->results[$outcome->unitTitle][$outcome->testTitle] = [
            'resolution' => $testResolution,
            'assertions' => $outcome->assertions,
        ];
        $this->testTotals[$testResolution->value]++;

        foreach ($outcome->assertions as $assertion) {
            $this->assertionTotals[$assertion->resolution->value]++;
        }

        $symbol = $this->symbolForResolution($testResolution);

        $this->lineBuffer .= $symbol;
        $columnsCount = 20;

        $percent = $this->testsCount > 0
            ? (int)floor($this->testsProcessed / $this->testsCount * 100)
            : 100;

        $visible = mb_substr($this->lineBuffer, -$columnsCount);
        $padding = max(0, $columnsCount - mb_strlen($visible));
        $percentText = sprintf('%3d%%', $percent);

        echo "\r" . $visible . str_repeat(' ', $padding) . ' ' . $percentText;

        if ($this->testsProcessed % $columnsCount === 0 || $this->testsProcessed === $this->testsCount) {
            echo "\n";
            $this->lineBuffer = '';
        }
    }

    public function onEnd(): void
    {
        echo "\n";

        $hadProblems = false;
        foreach ($this->results as $unitTitle => $tests) {
            $problemTests = array_filter(
                $tests,
                fn(array $t) => $t['resolution'] !== AssertResolution::Success
            );

            if (!$problemTests) {
                continue;
            }

            $hadProblems = true;
            printf("Unit: %s\n", $unitTitle);

            foreach ($problemTests as $testTitle => $info) {
                printf("  Test: %s [%s]\n", $testTitle, $this->labelFor($info['resolution']));

                /** @var Assertion $a */
                foreach ($info['assertions'] as $a) {
                    if ($a->resolution === AssertResolution::Success) {
                        continue;
                    }

                    $file = $a->file ?? '';
                    $line = $a->line ?? 0;
                    $title = $a->title ?? '';
                    $desc = $a->description ?? '';

                    printf(
                        "    %s %s(%d)\n      %s%s\n",
                        $this->symbolFor($a),
                        $file,
                        (int)$line,
                        $title ? $title . '. ' : '',
                        $desc
                    );
                }
            }

            echo "\n";
        }

        if (!$hadProblems) {
            echo "All tests passed ✅\n";
        }

        $testsTotal = $this->testsCount;
        $assertsTotal = array_sum($this->assertionTotals);

        echo "Totals\n";
        printf(
            "  Tests: %d  (passed: %d, failed: %d, errors: %d, warnings: %d, incomplete: %d, skipped: %d)\n",
            $testsTotal,
            $this->testTotals[AssertResolution::Success->value],
            $this->testTotals[AssertResolution::Failed->value],
            $this->testTotals[AssertResolution::Error->value],
            $this->testTotals[AssertResolution::Warning->value],
            $this->testTotals[AssertResolution::Incomplete->value],
            $this->testTotals[AssertResolution::Skipped->value],
        );

        printf(
            "  Assertions: %d  (passed: %d, failed: %d, errors: %d, warnings: %d, incomplete: %d, skipped: %d)\n",
            $assertsTotal,
            $this->assertionTotals[AssertResolution::Success->value],
            $this->assertionTotals[AssertResolution::Failed->value],
            $this->assertionTotals[AssertResolution::Error->value],
            $this->assertionTotals[AssertResolution::Warning->value],
            $this->assertionTotals[AssertResolution::Incomplete->value],
            $this->assertionTotals[AssertResolution::Skipped->value],
        );
    }

    private function symbolFor(Assertion $a): string
    {
        return $this->symbolForResolution($a->resolution);
    }

    private function symbolForResolution(AssertResolution $r): string
    {
        return match ($r) {
            AssertResolution::Failed => 'F',
            AssertResolution::Error => 'E',
            AssertResolution::Warning => 'W',
            AssertResolution::Incomplete => 'I',
            AssertResolution::Skipped => 'S',
            AssertResolution::Success => '.',
        };
    }

    private function labelFor(AssertResolution $r): string
    {
        return match ($r) {
            AssertResolution::Failed => 'Failed',
            AssertResolution::Error => 'Error',
            AssertResolution::Warning => 'Warning',
            AssertResolution::Incomplete => 'Incomplete',
            AssertResolution::Skipped => 'Skipped',
            AssertResolution::Success => 'Success',
        };
    }
}
