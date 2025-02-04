<?php declare(strict_types=1);

namespace AP\Routing\Middleware;

use AP\Routing\Request\Request;
use AP\Routing\Response\Response;

interface BeforeInterface
{
    /**
     * Executes pre-processing logic before handling a request.
     *
     * - If a `Response` object is returned, request processing stops immediately, and the response is sent to the client.
     * - If `null` is returned, the request continues through the next middleware or handler.
     *
     * @param Request $request The incoming HTTP request.
     * @return Response|null A response to be sent immediately, or `null` to continue processing the request.
     */
    public function before(Request $request): ?Response;
}