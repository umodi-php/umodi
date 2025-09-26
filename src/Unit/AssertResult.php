<?php

declare(strict_types=1);

namespace umodi\src\Unit;

class AssertResult
{
    public function __construct(
        public readonly AssertResolution $resolution,
        public readonly string $description,
    )
    {
    }
}
