<?php

declare(strict_types=1);

namespace umodi\src\Unit\Di;

final class Resolution
{
    public function __construct(
        public bool $ok,
        public mixed $value = null
    ) {}
    public static function hit(mixed $value): self { return new self(true, $value); }
    public static function miss(): self { return new self(false, null); }
}
