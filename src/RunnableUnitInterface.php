<?php

declare(strict_types=1);

namespace Umodi;

interface RunnableUnitInterface
{
    public function runBefore(): void;
    public function runAfter(): void;
    public function runBeforeEach(): void;
    public function runAfterEach(): void;
    public function getTests(): array;
}
