<?php

declare(strict_types=1);

namespace umodi\src\Unit\Di;

use ReflectionParameter;

interface ParameterResolverInterface
{
    /**
     * Попытаться разрешить параметр.
     * Возвращает объект Resolution: ok=true и value — если удалось; ok=false — если нет.
     *
     * @param array $provided Карта заранее предоставленных значений (по имени и/или по FQCN).
     * @param array $context  Любой сопутствующий контекст вызова (например, текущий Unit).
     */
    public function resolve(ReflectionParameter $param, array $provided, array $context = []): Resolution;
}
