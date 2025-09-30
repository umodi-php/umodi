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
        private readonly ?ParameterResolverInterface $resolver = null,
    )
    {
    }

    public function invoke(callable $callable, array $provided = []): mixed
    {
        $ref = $this->reflectCallable($callable);
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $name = $param->getType()?->getName();
            if (array_key_exists($name, $provided)) {
                $args[] = Resolution::hit(self::eval($provided[$name]))->value;
                continue;
            }

            $res = $this->resolver?->resolve($param);
            if ($res !== null && $res->ok) {
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

    private static function eval(mixed $v): mixed
    {
        return is_callable($v) ? $v() : $v;
    }
}
