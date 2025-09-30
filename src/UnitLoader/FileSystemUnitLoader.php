<?php

declare(strict_types=1);

namespace Umodi\UnitLoader;

use DirectoryIterator;
use function Umodi\_unit;

class FileSystemUnitLoader implements UnitLoaderInterface
{
    public function __construct(private readonly string $path)
    {
    }

    public function load(): iterable
    {
        foreach (new DirectoryIterator($this->path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            include_once $fileInfo->getRealPath();
        }

        return _unit();
    }
}
