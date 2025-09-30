<?php

declare(strict_types=1);

namespace Umodi\Di;

use ReflectionParameter;

final class CompositeResolver implements ParameterResolverInterface
{
    /** @param ParameterResolverInterface[] $chain */
    public function __construct(private readonly array $chain) {}

    public function resolve(ReflectionParameter $param): Resolution
    {
        foreach ($this->chain as $r) {
            $res = $r->resolve($param);
            if ($res->ok) return $res;
        }
        return Resolution::miss();
    }
}
