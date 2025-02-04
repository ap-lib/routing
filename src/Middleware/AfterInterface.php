<?php declare(strict_types=1);

namespace AP\Routing\Middleware;

use AP\Routing\Request\Request;
use AP\Routing\Response\Response;

interface AfterInterface
{
    /**
     * Executes post-processing logic after handling a request.
     *
     * - If `true` is returned, the response is immediately sent to the client, and further middleware execution is stopped.
     * - If `false` is returned, the request continues through the next middleware in the pipeline.
     *
     * @param Request $request The incoming HTTP request.
     * @param Response &$response The response object, which can be modified before being sent.
     * @return bool `true` to finalize the response immediately, `false` to allow further middleware processing.
     */
    public function after(Request $request, Response &$response): bool;
}