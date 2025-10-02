<?php

declare(strict_types=1);

namespace Umodi\Result;

use Umodi\Exception\StopTestException;
use Umodi\Severity\AssertResolution;

class AssertCollector
{
    /**
     * @var Assertion[] $assertions
     */
    public array $assertions = [];

    public function assert(AssertResult $assertResult, string $title = '', bool $stopOnFailure = false)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->assertions[] = new Assertion(
            $title,
            $assertResult->description,
            $assertResult->resolution,
            $backtrace[0]['file'],
            $backtrace[0]['line'],
        );

        if ($stopOnFailure && $assertResult->resolution !== AssertResolution::Success) {
            throw new StopTestException('Stopped by assertion');
        }
    }

    public function skip(AssertResult $assertResult, string $title = '', bool $stopOnFailure = false)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->assertions[] = new Assertion(
            $title,
            '',
            AssertResolution::Skipped,
            $backtrace[0]['file'],
            $backtrace[0]['line'],
        );

        if ($stopOnFailure) {
            throw new StopTestException('Stopped by skip');
        }
    }

    public function assertOrStop(AssertResult $r, string $title = ''): void
    {
        $this->assert($r, $title, true);
    }
}
