<?php

declare(strict_types=1);

namespace Umodi\Di\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionParameter;
use Umodi\Di\Resolution;

final class PsrContainerResolver implements ParameterResolverInterface
{
    public function __construct(private readonly ContainerInterface $c)
    {
    }

    public function resolve(ReflectionParameter $param): Resolution
    {
        $type = $param->getType();
        $class = $type && !$type->isBuiltin() ? (string)$type : null;
        if ($class && $this->c->has($class)) {
            return Resolution::hit($this->c->get($class));
        }
        return Resolution::miss();
    }
}
