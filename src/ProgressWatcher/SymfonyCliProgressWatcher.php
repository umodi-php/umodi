<?php

declare(strict_types=1);

namespace Umodi\ProgressWatcher;

use Symfony\Component\Console\Output\Output;
use Umodi\Assertion;
use Umodi\AssertResolution;
use Umodi\Unit;
use Unit\AssertCollector;

class SymfonyCliProgressWatcher implements ProgressWatcherInterface
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

    public function __construct(private readonly Output $output)
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
        // можно будет вывести заголовок юнита здесь, если нужно
    }

    public function onTestResult(string $unitTitle, Unit $unit, string $testTitle, AssertCollector $assertCollector): void
    {
        $this->testsProcessed++;

        $testResolution = AssertResolution::Success;

        foreach ($assertCollector->assertions as $assertion) {
            $this->assertionTotals[$assertion->resolution->value]++;

            if ($this->isWorse($assertion->resolution, $testResolution)) {
                $testResolution = $assertion->resolution;
            }
        }

        $this->results[$unitTitle][$testTitle] = [
            'resolution' => $testResolution,
            'assertions' => $assertCollector->assertions,
        ];
        $this->testTotals[$testResolution->value]++;

        // символ для прогресса
        $symbol = $this->symbolForResolution($testResolution);

        // рисуем прогресс-строку (как у тебя)
        $this->lineBuffer .= $symbol;
        $columnsCount = 20;

        $percent = $this->testsCount > 0
            ? (int)floor($this->testsProcessed / $this->testsCount * 100)
            : 100;

        $visible = mb_substr($this->lineBuffer, -$columnsCount);
        $padding = max(0, $columnsCount - mb_strlen($visible));
        $percentText = sprintf('%3d%%', $percent);

        if ($this->output->isDecorated()) {
            $this->output->write("\r" . $visible . str_repeat(' ', $padding) . ' ' . $percentText);
        } else {
            $this->output->write($symbol);
            if ($this->testsProcessed % $columnsCount === 0 || $this->testsProcessed === $this->testsCount) {
                $this->output->writeln(' ' . $percentText);
            }
        }

        if ($this->testsProcessed % $columnsCount === 0 || $this->testsProcessed === $this->testsCount) {
            if ($this->output->isDecorated()) {
                $this->output->writeln('');
            }
            $this->lineBuffer = '';
        }
    }

    public function onEnd(): void
    {
        $this->output->writeln('');

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
            $this->output->writeln(sprintf('<info>Unit: %s</info>', $unitTitle));

            foreach ($problemTests as $testTitle => $info) {
                $this->output->writeln(sprintf('  <comment>Test: %s</comment> [%s]', $testTitle, $this->labelFor($info['resolution'])));

                /** @var Assertion $a */
                foreach ($info['assertions'] as $a) {
                    if ($a->resolution === AssertResolution::Success) {
                        continue;
                    }

                    $file = $a->file ?? '';
                    $line = $a->line ?? 0;
                    $title = $a->title ?? '';
                    $desc = $a->description ?? '';

                    $this->output->writeln(sprintf(
                        "    %s %s(%d)\n      %s%s",
                        $this->symbolFor($a),
                        $file,
                        (int)$line,
                        $title ? $title . '. ' : '',
                        $desc
                    ));
                }
            }

            $this->output->writeln(''); // разделитель между юнитами
        }

        if (!$hadProblems) {
            $this->output->writeln('<info>All tests passed ✅</info>');
        }

        // 2) Тоталы
        $testsTotal = $this->testsCount;
        $assertsTotal = array_sum($this->assertionTotals);

        $this->output->writeln('<info>Totals</info>');
        $this->output->writeln(sprintf(
            '  Tests: %d  (passed: %d, failed: %d, errors: %d, warnings: %d, incomplete: %d, skipped: %d)',
            $testsTotal,
            $this->testTotals[AssertResolution::Success->value],
            $this->testTotals[AssertResolution::Failed->value],
            $this->testTotals[AssertResolution::Error->value],
            $this->testTotals[AssertResolution::Warning->value],
            $this->testTotals[AssertResolution::Incomplete->value],
            $this->testTotals[AssertResolution::Skipped->value],
        ));

        $this->output->writeln(sprintf(
            '  Assertions: %d  (ok: %d, failed: %d, errors: %d, warnings: %d, incomplete: %d, skipped: %d)',
            $assertsTotal,
            $this->assertionTotals[AssertResolution::Success->value],
            $this->assertionTotals[AssertResolution::Failed->value],
            $this->assertionTotals[AssertResolution::Error->value],
            $this->assertionTotals[AssertResolution::Warning->value],
            $this->assertionTotals[AssertResolution::Incomplete->value],
            $this->assertionTotals[AssertResolution::Skipped->value],
        ));
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

    /** true, если $a «хуже», чем $b (по приоритету ошибок) */
    private function isWorse(AssertResolution $a, AssertResolution $b): bool
    {
        return $this->severity($a) > $this->severity($b);
    }

    /** чем больше число — тем хуже */
    private function severity(AssertResolution $r): int
    {
        return match ($r) {
            AssertResolution::Success => 0,
            AssertResolution::Skipped => 1,
            AssertResolution::Incomplete => 2,
            AssertResolution::Warning => 3,
            AssertResolution::Failed => 4,
            AssertResolution::Error => 5,
        };
    }
}
