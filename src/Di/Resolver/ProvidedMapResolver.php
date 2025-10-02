<?php

declare(strict_types=1);

namespace Umodi\Di\Resolver;

use ReflectionParameter;
use Umodi\Di\Resolution;

final class ProvidedMapResolver implements ParameterResolverInterface
{
    public function __construct(private readonly array $globalProvided = []) {}

    public function resolve(ReflectionParameter $param, array $provided): Resolution
    {
        $all = $provided + $this->globalProvided;

        $name = $param->getName();
        if (array_key_exists($name, $all)) {
            return Resolution::hit(self::eval($all[$name]));
        }

        $type = $param->getType();
        $class = $type && !$type->isBuiltin() ? (string)$type : null;

        if ($class && array_key_exists($class, $all)) {
            return Resolution::hit(self::eval($all[$class]));
        }

        // Фоллбэк: ищем первый instanceof среди provided
        if ($class) {
            foreach ($all as $v) {
                $v = self::eval($v, lazyOk: true);
                if (is_object($v) && is_a($v, $class)) {
                    return Resolution::hit($v);
                }
            }
        }

        return Resolution::miss();
    }

    private static function eval(mixed $v, bool $lazyOk = false): mixed
    {
        return is_callable($v) ? $v() : $v;
    }
}
