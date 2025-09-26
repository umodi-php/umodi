<?php

declare(strict_types=1);

namespace Umodi;

enum AssertResolution: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Skipped = 'skipped';
    case Warning  = 'warning';
    case Error  = 'error';
    case Risky  = 'risky';
    case Incomplete = 'incomplete';
}
