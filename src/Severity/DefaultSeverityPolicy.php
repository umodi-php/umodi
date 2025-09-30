<?php

declare(strict_types=1);

namespace Umodi\Severity;

final class DefaultSeverityPolicy implements SeverityPolicyInterface {
    private const ORDER = [
        AssertResolution::Success->value => AssertResolution::Success,
        AssertResolution::Skipped->value => AssertResolution::Skipped,
        AssertResolution::Incomplete->value => AssertResolution::Incomplete,
        AssertResolution::Warning->value => AssertResolution::Warning,
        AssertResolution::Risky->value => AssertResolution::Risky,
        AssertResolution::Failed->value => AssertResolution::Failed,
        AssertResolution::Error->value => AssertResolution::Error,
    ];
    public function worse(AssertResolution $a, AssertResolution $b): AssertResolution {
        $pos = array_flip(self::ORDER);
        return ($pos[$a->value] > $pos[$b->value]) ? $a : $b;
    }
}
