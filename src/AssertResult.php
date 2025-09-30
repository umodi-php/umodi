<?php

declare(strict_types=1);

namespace Umodi;

use Umodi\Severity\AssertResolution;

class AssertResult
{
    public function __construct(
        public readonly AssertResolution $resolution,
        public readonly string $description,
    )
    {
    }
}
