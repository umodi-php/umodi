<?php

declare(strict_types=1);

namespace Umodi\Exception;

class InjectionException extends \RuntimeException
{
    public function __construct(string $hint, callable $callable)
    {
        parent::__construct("Can't inject {$hint} for {$this->callableToString($callable)}");
    }

    private function callableToString(callable $c): string
    {
        if (is_array($c)) {
            [$objOrClass, $method] = $c;
            if (is_object($objOrClass)) {
                return get_class($objOrClass) . "->{$method}()";
            }
            return $objOrClass . "::{$method}()";
        }
        if ($c instanceof \Closure) {
            return 'Closure';
        }
        if (is_object($c) && method_exists($c, '__invoke')) {
            return get_class($c) . '::__invoke()';
        }
        if (is_string($c)) {
            return $c;
        }
        return 'callable';
    }
}
