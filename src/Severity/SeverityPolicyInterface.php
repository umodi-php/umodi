<?php

declare(strict_types=1);

namespace Umodi\Severity;

interface SeverityPolicyInterface
{
    public function worse(AssertResolution $a, AssertResolution $b): AssertResolution;
}
