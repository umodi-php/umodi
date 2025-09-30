<?php

declare(strict_types=1);

namespace Umodi\UnitLoader;

interface UnitLoaderInterface
{
    /**
     * @return array<string, callable>
     */
    public function load(): iterable;
}
