<?php

declare(strict_types=1);

namespace Umodi\Severity;

interface ExceptionClassifierInterface {
    public function classify(\Throwable $e): AssertResolution;
}
