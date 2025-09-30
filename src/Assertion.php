<?php

declare(strict_types=1);

namespace Umodi;

use Umodi\Severity\AssertResolution;

readonly class Assertion
{
    public function __construct(
        public string           $title,
        public string           $description,
        public AssertResolution $resolution,
        public string           $file,
        public int              $line,
    )
    {
    }
}
