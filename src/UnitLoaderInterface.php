<?php

declare(strict_types=1);

namespace Umodi;

interface UnitLoaderInterface
{
    /**
     * @return array<string, callable>
     */
    public function load(): array;
}
