<?php

declare(strict_types=1);

namespace Umodi\Di\Invoker;

interface InvokerInterface
{
    public function invoke(callable $callable, array $provided = []): mixed;
}
