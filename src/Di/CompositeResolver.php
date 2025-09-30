<?php

declare(strict_types=1);

namespace Umodi\Di;

use ReflectionParameter;

final class CompositeResolver implements ParameterResolverInterface
{
    /** @param ParameterResolverInterface[] $chain */
    public function __construct(private readonly array $chain) {}

    public function resolve(ReflectionParameter $param, array $provided): Resolution
    {
        foreach ($this->chain as $r) {
            $res = $r->resolve($param, $provided);
            if ($res->ok) return $res;
        }
        return Resolution::miss();
    }
}
