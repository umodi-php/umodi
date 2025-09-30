<?php

declare(strict_types=1);

namespace Umodi\Severity;

use Umodi\Exception\TestPreconditionFailedException;

final class DefaultExceptionClassifier implements ExceptionClassifierInterface {
    public function classify(\Throwable $e): AssertResolution {
        return $e instanceof TestPreconditionFailedException
            ? AssertResolution::Skipped
            : AssertResolution::Error;
    }
}
