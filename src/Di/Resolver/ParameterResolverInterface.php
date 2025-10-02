<?php

declare(strict_types=1);

namespace Umodi\Di\Resolver;

use ReflectionParameter;
use Umodi\Di\Resolution;

interface ParameterResolverInterface
{
    public function resolve(ReflectionParameter $param): Resolution;
}
