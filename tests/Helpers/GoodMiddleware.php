<?php declare(strict_types=1);

namespace AP\Routing\Tests\Helpers;

use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Request\Request;
use AP\Routing\Response\Response;

class GoodMiddleware implements BeforeInterface
{
    public function before(Request $request): ?Response
    {
        return null;
    }
}