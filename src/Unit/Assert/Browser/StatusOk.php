<?php

declare(strict_types=1);

namespace umodi\src\Unit\Assert\Browser;

use Symfony\Component\HttpFoundation\Response;
use umodi\src\Unit\Assert\AssertInterface;
use umodi\src\Unit\AssertResolution;
use umodi\src\Unit\AssertResult;

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
