<?php

declare(strict_types=1);

namespace Umodi\Assert\Browser;

use Symfony\Component\HttpFoundation\Response;
use Umodi\Assert\AssertInterface;
use Umodi\AssertResolution;
use Umodi\AssertResult;

class StatusOk implements AssertInterface
{
    public static function a(Response $response): AssertResult
    {
        return new AssertResult(
            $response->getStatusCode() === 200
                ? AssertResolution::Success
                : AssertResolution::Failed,
            ''
        );
    }
}
