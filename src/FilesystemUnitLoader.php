<?php

declare(strict_types=1);

namespace Umodi;

use DirectoryIterator;

final class FilesystemUnitLoader implements UnitLoaderInterface
{
    public function __construct(
        private readonly string $testsDirectory
    ) {
    }

    public function load(): array
    {
        foreach (new DirectoryIterator($this->testsDirectory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            include_once $fileInfo->getRealPath();
        }

        return _unit();
    }
}
