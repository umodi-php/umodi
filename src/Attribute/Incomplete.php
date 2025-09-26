<?php

declare(strict_types=1);

namespace Umodi\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Incomplete
{
    public function __construct(public string $reason) {}
}
