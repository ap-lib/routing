<?php declare(strict_types=1);

namespace AP\Routing\Response;

use JsonException;

/**
 * JSON response class extending Response.
 *
 * This class ensures the response body is properly JSON-encoded and
 * automatically sets the `Content-Type` header to `application/json`.
 */
class Json extends Response
{
    /**
     * Creates a JSON response.
     *
     * If the body is an array, it is automatically converted to a JSON string.
     * The `Content-Type` header is set to `application/json`.
     *
     * @param array|string $body The response data, either a JSON-encoded string or an array to be encoded.
     * @param int $code The HTTP status code (default: 200).
     * @throws JsonException
     */
    public function __construct(
        array|string $body,
        int          $code = 200,
    )
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        parent::__construct(
            body: $body,
            code: $code,
        );

        $this->addHeader("Content-Type", "application/json");
    }
}