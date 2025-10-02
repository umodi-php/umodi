<?php

declare(strict_types=1);

namespace Umodi\UnitLoader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function Umodi\_unit;

class FileSystemUnitLoader implements UnitLoaderInterface
{
    public function __construct(private readonly string $path)
    {
    }

    public function load(): iterable
    {
        $directory = $this->path;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $it->rewind();
        while($it->valid()) {
            if (!$it->isDot()) {
                include_once $it->key();
            }

            $it->next();
        }
        return _unit();
    }
}
