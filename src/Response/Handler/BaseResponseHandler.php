<?php declare(strict_types=1);

namespace AP\Routing\Response\Handler;

use AP\Routing\Response\Json;
use AP\Routing\Response\Response;
use Generator;
use JsonException;
use RuntimeException;

/**
 * Default implementation of ResponseHandlerInterface.
 *
 * This class ensures that handler responses are consistently transformed into a Response object.
 */
class BaseResponseHandler implements ResponseHandlerInterface
{
    /**
     * Converts a handler's raw response into a Response object.
     *
     * - If the response is a string, it is wrapped in a `Response` object.
     * - If the response is an array, it is converted into a `Json` response.
     * - If the response is already a `Response` object, it is returned as is.
     * - If the response type is unsupported, a `RuntimeException` is thrown.
     *
     * @param mixed $response The raw response from a handler.
     * @return Response The standardized Response object.
     * @throws RuntimeException If the response is neither a string, array, nor Response object
     * @throws JsonException
     */
    public function convert(mixed $response): Response
    {
        if (is_string($response) || $response instanceof Generator) {
            $response = new Response($response);
        } elseif (is_array($response)) {
            $response = new Json($response);
        }

        if ($response instanceof Response) {
            return $response;
        }
        throw new RuntimeException(
            "response must be array, string, Generator, or Response"
        );
    }
}