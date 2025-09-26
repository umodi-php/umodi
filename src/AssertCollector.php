<?php

declare(strict_types=1);

namespace Umodi;

class AssertCollector
{
    /**
     * @var Assertion[] $assertions
     */
    public array $assertions = [];

    public function assert(AssertResult $assertResult, string $title = '')
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->assertions[] = new Assertion(
            $title,
            $assertResult->description,
            $assertResult->resolution,
            $backtrace[0]['file'],
            $backtrace[0]['line'],
        );
    }

    public function skip(AssertResult $assertResult, string $title = '')
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $this->assertions[] = new Assertion(
            $title,
            '',
            AssertResolution::Skipped,
            $backtrace[0]['file'],
            $backtrace[0]['line'],
        );
    }
}
