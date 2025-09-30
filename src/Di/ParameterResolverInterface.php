<?php

declare(strict_types=1);

namespace Umodi\Di;

use ReflectionParameter;

interface ParameterResolverInterface
{
    public function resolve(ReflectionParameter $param): Resolution;
}
