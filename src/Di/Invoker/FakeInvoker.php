<?php

declare(strict_types=1);

namespace Umodi\Di\Invoker;

class FakeInvoker implements InvokerInterface
{
    public function invoke(callable $callable, array $provided = []): mixed {
        return $callable(...array_values($provided));
    }
}
