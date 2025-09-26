<?php

declare(strict_types=1);

namespace Umodi;

class AssertResult
{
    public function __construct(
        public readonly AssertResolution $resolution,
        public readonly string $description,
    )
    {
    }
}
