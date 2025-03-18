<?php declare(strict_types=1);

namespace AP\Routing\Request;

use AP\Context\Context;

class Request
{
    /**
     * Represents an HTTP request with various properties, including method, headers, and request data.
     *
     * This class is immutable, `readonly` properties, except for `context`, which allows storing request-related metadata.
     *
     * @param Method $method The HTTP method of the request
     * @param string $path The request path, URL without domain
     * @param array<string, string> $get Query parameters from the URL
     * @param array<string, string> $post Form data from a POST request
     * @param array<string, string> $cookie Cookies sent by the client
     * @param array<string, string> $headers HTTP request headers
     * @param array<string, mixed> $files Uploaded files
     * @param string $body The raw request body, useful for JSON, XML, or other raw payloads
     * @param array<string, string> $params Path parameters extracted from the route definition. These are dynamically mapped based on routing configuration
     * @param string $ip User's request ip address
     * @param Context $context A mutable context object for storing metadata exchanged between middleware and the handler
     */
    public function __construct(
        readonly public Method $method,
        readonly public string $path,
        readonly public array  $get,
        readonly public array  $post,
        readonly public array  $cookie,
        readonly public array  $headers,
        readonly public array  $files,
        readonly public string $body,
        readonly public array  $params,
        readonly public string $ip,
        public Context         $context = new Context()
    )
    {
    }
}