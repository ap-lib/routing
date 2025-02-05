<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing\Middleware\TestBefore;

use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Request\Request;
use AP\Routing\Response\Response;

readonly class BeforeMiddleware implements BeforeInterface
{
    public function __construct(
        private string $if_exist_get_value,
        private string $return_string,
    )
    {
    }

    public function before(Request $request): ?Response
    {
        if (key_exists($this->if_exist_get_value, $request->get)) {
            return new Response($this->return_string);
        }
        return null;
    }
}