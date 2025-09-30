<?php

declare(strict_types=1);

namespace Umodi\Result;

use Umodi\Severity\AssertResolution;

final class TestOutcome {
    public function __construct(
        public readonly string $unitTitle,
        public readonly string $testTitle,
        public readonly AssertResolution $resolution,
        /** @var Assertion[] */ public readonly array $assertions
    ) {}
}
