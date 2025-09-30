<?php

declare(strict_types=1);

namespace Umodi\Severity;

final class DefaultSeverityPolicy implements SeverityPolicyInterface {
    private const ORDER = [
        AssertResolution::Success->value,
        AssertResolution::Skipped->value,
        AssertResolution::Incomplete->value,
        AssertResolution::Warning->value,
        AssertResolution::Risky->value,
        AssertResolution::Failed->value,
        AssertResolution::Error->value,
    ];
    public function worse(AssertResolution $a, AssertResolution $b): AssertResolution {
        $pos = array_flip(self::ORDER);
        return ($pos[$a->value] > $pos[$b->value]) ? $a : $b;
    }
}
