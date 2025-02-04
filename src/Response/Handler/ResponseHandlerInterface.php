<?php declare(strict_types=1);

namespace AP\Routing\Response\Handler;

use AP\Routing\Response\Response;
use Throwable;

/**
 * Interface for converting various types of handler responses into a standardized Response object.
 */
interface ResponseHandlerInterface
{
    /**
     * Converts a raw response into a Response object.
     *
     * @param mixed $response The raw response from a handler.
     * @return Response The standardized Response object.
     * @throws Throwable If the response type is invalid.
     */
    public function convert(mixed $response): Response;
}