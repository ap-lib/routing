<?php declare(strict_types=1);

namespace AP\Routing\Tests\Helpers;

use AP\Routing\Middleware\AfterInterface;
use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Request\Request;
use AP\Routing\Response\Response;
use Closure;
use Exception;

readonly class ClosureMiddleware implements BeforeInterface, AfterInterface
{
    public function __construct(
        private ?Closure $before = null,
        private ?Closure $after = null,
    )
    {
    }

    /**
     * @throws Exception
     */
    final public function after(Request $request, Response &$response): bool
    {
        if ($this->after instanceof Closure) {
            $res = ($this->after)($request, $response);
            if (is_bool($res)) {
                return $res;
            } else {
                throw new Exception("invalid after return");
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    final public function before(Request $request): ?Response
    {
        if ($this->before instanceof Closure) {
            $res = ($this->before)($request);
            if (is_null($res) || $res instanceof Response) {
                return $res;
            } else {
                throw new Exception("invalid before response");
            }
        }
        return null;
    }
}