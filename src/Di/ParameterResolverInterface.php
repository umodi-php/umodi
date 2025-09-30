<?php

declare(strict_types=1);

namespace Umodi\Di;

use ReflectionParameter;

interface ParameterResolverInterface
{
    /**
     * Попытаться разрешить параметр.
     * Возвращает объект Resolution: ok=true и value — если удалось; ok=false — если нет.
     *
     * @param array $provided Карта заранее предоставленных значений (по имени и/или по FQCN).
     */
    public function resolve(ReflectionParameter $param, array $provided): Resolution;
}
