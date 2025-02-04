<?php declare(strict_types=1);

namespace AP\Routing\Response;

use Closure;
use Generator;
use UnexpectedValueException;

class Response
{
    /**
     * Optional link to any callable method/function will be called after a client
     * gets full response and connection will be closed
     *
     * @var array<Closure|string|array>
     */
    protected array $postProcess = [];

    /**
     * HTTP headers associative array where keys are header names and values are their corresponding values
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * Initializes a new HTTP response instance.
     *
     *
     * @param Generator|string $body The response body, which can be a string or a Generator for streaming responses
     * @param int $code The HTTP status code, default: 200 OK
     */
    public function __construct(
        /**
         * @var Generator<string>|string
         */
        public Generator|string $body = "",
        public int              $code = 200,
    )
    {
    }

    /**
     * Adds or removes an HTTP header for the response
     *
     * If a valid string value is provided, the header is set
     * If the value is null, the header is removed
     *
     * @param string $name The name of the header
     * @param string|null $value The value of the header. If null, the header is removed
     * @return $this
     */
    public function addHeader(string $name, ?string $value): static
    {
        // Normalize header name to proper casing, for example, "Content-Type"
        $name = ucwords(strtolower($name), '-');

        if (is_string($value)) {
            $this->headers[$name] = $value;
        } elseif (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        return $this;
    }

    /**
     * Retrieves all HTTP headers set for the response
     * An associative array where keys are header names and values are their corresponding values
     *
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Registers a callback to be executed after the response has been sent, and the connection is closed
     *
     * This allows for deferred execution of tasks such as logging, cleanup, or background processing
     * without blocking the client request
     *
     * @param Closure|string|array $callable
     * @return $this
     * @throws UnexpectedValueException If the provided value isn't validly callable
     */
    public function addPostProcessCallback(Closure|string|array $callable): static
    {
        if (!is_callable($callable)) {
            throw new UnexpectedValueException("value `callable` must be valid for is_callable()");
        }
        $this->postProcess[] = $callable;
        return $this;
    }

    /**
     * Retrieves all registered post-processing callbacks
     *
     * These callbacks are executed after the response has been sent and the connection is closed,
     * they can be used for tasks such as logging, cleanup, or background processing
     *
     * @return array<Closure|string|array>
     */
    public function getPostProcessCallbacks(): array
    {
        return $this->postProcess;
    }
}