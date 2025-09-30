<?php

declare(strict_types=1);

namespace Umodi\Di;

use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Umodi\Exception\InjectionException;

class Invoker
{
    public function __construct(
        private readonly ParameterResolverInterface $resolver,
    )
    {
    }

    public function invoke(callable $callable, array $provided = [], array $context = []): mixed
    {
        $ref = $this->reflectCallable($callable);
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $res = $this->resolver->resolve($param, $provided);
            if ($res->ok) {
                $args[] = $res->value;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            $type = $param->getType();
            $class = $type && !$type->isBuiltin() ? (string)$type : null;
            $hint = $class ? "type {$class}" : "name \${$param->getName()}";
            throw new InjectionException($hint, $callable);
        }

        return $callable(...$args);
    }

    public function reflectCallable(callable $c): ReflectionFunctionAbstract
    {
        if ($c instanceof \Closure) {
            return new ReflectionFunction($c);
        }
        if (is_array($c)) {
            [$objOrClass, $method] = $c;
            return new ReflectionMethod($objOrClass, $method);
        }
        if (is_object($c) && method_exists($c, '__invoke')) {
            return new ReflectionMethod($c, '__invoke');
        }
        if (is_string($c) && function_exists($c)) {
            return new ReflectionFunction($c);
        }

        return new ReflectionFunction(\Closure::fromCallable($c));
    }
}
