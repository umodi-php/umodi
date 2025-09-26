<?php

declare(strict_types=1);

namespace umodi\src\Unit\ProgressWatcher;

use umodi\src\Unit\Assertion;
use umodi\src\Unit\AssertResolution;
use umodi\src\Unit\Unit;
use Unit\AssertCollector;

final class JunitXmlProgressWatcher implements ProgressWatcherInterface
{
    private string $file;
    /** @var array<string, array<string, array{resolution: AssertResolution, assertions: Assertion[]}>> */
    private array $results = [];
    private int $tests = 0, $failures = 0, $errors = 0, $skipped = 0;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function onStart(array $units): void
    {
    }

    public function onUnitStart(string $unitTitle, Unit $unit): void
    {
    }

    public function onTestResult(string $unitTitle, Unit $unit, string $testTitle, AssertCollector $assertCollector): void
    {
        $res = AssertResolution::Success;
        foreach ($assertCollector->assertions as $a) {
            if ($this->severity($a->resolution) > $this->severity($res)) {
                $res = $a->resolution;
            }
        }
        $this->results[$unitTitle][$testTitle] = ['resolution' => $res, 'assertions' => $assertCollector->assertions];

        $this->tests++;
        $this->failures += (int)($res === AssertResolution::Failed);
        $this->errors += (int)($res === AssertResolution::Error);
        $this->skipped += (int)($res === AssertResolution::Skipped || $res === AssertResolution::Incomplete);
    }

    public function onEnd(): void
    {
        $ts = date('c');
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = sprintf(
            '<testsuite name="UnitSuite" tests="%d" failures="%d" errors="%d" skipped="%d" timestamp="%s">',
            $this->tests, $this->failures, $this->errors, $this->skipped, $ts
        );

        foreach ($this->results as $unitTitle => $tests) {
            // Можно вложенные suites, но для простоты — classname=unitTitle
            foreach ($tests as $testTitle => $info) {
                /** @var AssertResolution $res */
                $res = $info['resolution'];
                $xml[] = sprintf(
                    '  <testcase classname="%s" name="%s" time="%s">',
                    $this->e($unitTitle), $this->e($testTitle), '0'
                );

                // Мэппинг статусов
                if ($res === AssertResolution::Failed || $res === AssertResolution::Warning) {
                    $msg = $this->firstMessage($info['assertions'], $res);
                    $xml[] = sprintf('    <failure type="%s" message="%s">%s</failure>',
                        $res === AssertResolution::Warning ? 'warning' : 'assertion',
                        $this->e($msg['short']),
                        $this->e($msg['long'])
                    );
                } elseif ($res === AssertResolution::Error) {
                    $msg = $this->firstMessage($info['assertions'], $res);
                    $xml[] = sprintf('    <error type="%s" message="%s">%s</error>',
                        'error', $this->e($msg['short']), $this->e($msg['long'])
                    );
                } elseif ($res === AssertResolution::Skipped || $res === AssertResolution::Incomplete) {
                    $msg = $this->firstMessage($info['assertions'], $res);
                    $xml[] = sprintf('    <skipped message="%s"/>', $this->e($msg['short']));
                }

                if (!empty($info['assertions'])) {
                    $xml[] = '    <system-out>' . $this->e($this->formatAssertions($info['assertions'])) . '</system-out>';
                }

                $xml[] = '  </testcase>';
            }
        }

        $xml[] = '</testsuite>';
        @mkdir(\dirname($this->file), 0777, true);
        file_put_contents($this->file, implode("\n", $xml));
    }

    private function e(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Возвращает первую подходящую запись по нужной резолюции */
    private function firstMessage(array $assertions, AssertResolution $want): array
    {
        foreach ($assertions as $a) {
            if ($a->resolution === $want) {
                $short = trim(($a->title ? $a->title . '. ' : '') . $a->description);
                $long = sprintf("%s:%d\n%s\n", $a->file, $a->line, $short);
                return ['short' => $short, 'long' => $long];
            }
        }
        return ['short' => $want->name, 'long' => $want->name];
    }

    private function formatAssertions(array $assertions): string
    {
        $lines = [];
        foreach ($assertions as $a) {
            $lines[] = sprintf('[%s] %s:%d — %s%s',
                $a->resolution->name,
                $a->file, $a->line,
                $a->title ? $a->title . '. ' : '',
                $a->description
            );
        }
        return implode("\n", $lines);
    }

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
