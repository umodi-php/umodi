<?php

declare(strict_types=1);

namespace Umodi\Severity;

use Umodi\AssertCollector;

final class TestResolutionAggregator {
    public function __construct(private readonly SeverityPolicyInterface $policy) {}
    public function aggregate(AssertCollector $ac): AssertResolution {
        $res = AssertResolution::Success;
        foreach ($ac->assertions as $a) {
            $res = $this->policy->worse($a->resolution, $res);
        }
        return $res;
    }
}
