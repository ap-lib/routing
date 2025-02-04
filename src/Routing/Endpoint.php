<?php declare(strict_types=1);

namespace AP\Routing\Routing;

use AP\Logger\Log;
use AP\Routing\Middleware\AfterInterface;
use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Request\Request;
use AP\Routing\Response\Handler\ResponseHandlerInterface;
use AP\Routing\Response\Response;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * Represents an endpoint in the routing system.
 *
 * An endpoint consists of a handler and optional middleware that
 * execute before and after the request is processed
 *
 *  ⚡ Performance Note:
 *      - Handlers and middleware must be references to static methods, for example "ClassName::method"
 *      - This avoids unnecessary object instantiation and improves execution efficiency
 */
class Endpoint
{
    protected ?array $middleware_objects = null;

    /**
     * @param string $handler The callable handler responsible for processing the request
     *                        It must be a static method reference and can have one of the following signatures:
     *                         `- fn(Request $request): Response|mixed`
     *                         `- fn(): Response|mixed`
     *
     * @param array<string> $middleware List of strings referencing static methods, where each method must return
     *                                  an object implementing `BeforeInterface` and/or `AfterInterface`
     *                                  Middleware static methods mustn't have any parameters
     */
    public function __construct(
        readonly public string $handler,
        readonly public array  $middleware = [],
    )
    {
    }

    /**
     * Validates that the handler and middleware are properly defined
     *
     * @param bool $validateMiddlewareObjects Validates the object returned from the static method
     *                                        This check is expensive and should only be used in backend workers
     *                                        when preparing the routing index, not during request handling
     * @return static
     *
     * @throws UnexpectedValueException If the handler isn't callable or if middleware is invalid.
     */
    public function validateException(bool $validateMiddlewareObjects = false): static
    {
        if (!is_callable($this->handler)) {
            throw new UnexpectedValueException("Handler must be callable");
        }

        foreach ($this->middleware as $mv) {
            if (!is_string($mv) || !is_callable($mv)) {
                throw new UnexpectedValueException("All middleware must be strings and callable");
            }
            if ($validateMiddlewareObjects) {
                $mv = $mv();
                if (!($mv instanceof BeforeInterface) && !($mv instanceof AfterInterface)) {
                    throw new UnexpectedValueException("middleware no implemented a required interface");
                }
            }
        }

        return $this;
    }

    /**
     * Retrieves instantiated middleware objects
     *
     * This method ensures that middleware instances are created only once
     * Only middleware implementing `BeforeInterface` or `AfterInterface` will be included
     *
     * @return array<string, BeforeInterface|AfterInterface> The list of middleware instances
     *                                                       key is callable link to static method
     */
    public function getMiddlewareObjects(): array
    {
        if (is_null($this->middleware_objects)) {
            $this->middleware_objects = [];
            foreach ($this->middleware as $middleware_callable) {
                $obj = $middleware_callable();
                if (($obj instanceof BeforeInterface) || ($obj instanceof AfterInterface)) {
                    $this->middleware_objects[$middleware_callable] = $obj;
                } else {
                    Log::error(
                        "middleware does not implement a required interface",
                        [
                            "middleware" => $middleware_callable
                        ],
                        "routing"
                    );
                }
            }
        }
        return $this->middleware_objects;
    }

    /**
     * Serializes the endpoint into a string representation
     *
     * @return string The serialized representation of the endpoint
     */
    public function serialize(): string
    {
        return implode(
            ",",
            array_merge([$this->handler], $this->middleware)
        );
    }

    /**
     * Deserializes a string representation into an `Endpoint` instance
     *
     * @param string $data The serialized endpoint data
     * @return static The deserialized `Endpoint` instance
     */
    public static function deserialize(string $data): static
    {
        $data = explode(",", $data);
        return new static(
            $data[0],
            array_slice($data, 1)
        );
    }

    /**
     * Executes the endpoint, processing middleware before and after the handler
     *
     * The request first passes through any `BeforeInterface` middleware
     * If middleware returns a response, execution stops early
     *
     * After calling the handler, the response is converted if a `ResponseHandlerInterface` is provided
     * Finally, any `AfterInterface` middleware is executed, and the response is returned
     *
     * @param Request $request The incoming HTTP request
     * @param ResponseHandlerInterface|null $responseHandler Optional response handler for converting responses
     * @return Response The processed response
     *
     * @throws RuntimeException If the handler or middleware produces an invalid response
     */
    public function run(Request $request, ?ResponseHandlerInterface $responseHandler = null): Response
    {
        foreach ($this->getMiddlewareObjects() as $name => $middleware) {
            if ($middleware instanceof BeforeInterface) {
                try {
                    $response = $middleware->before($request);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "middleware `{$name}->before()` exception: " . $e->getMessage(),
                        0,
                        $e
                    );
                }
                if (!is_null($response)) {
                    return $response;
                }
            }
        }

        try {
            $response = ($this->handler)($request);
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Handler `$this->handler` exception: " . $e->getMessage(),
                0,
                $e
            );
        }

        if ($responseHandler instanceof ResponseHandlerInterface) {
            try {
                $response = $responseHandler->convert($response);
            } catch (Throwable $e) {
                throw new RuntimeException(
                    "invalid handler response `$this->handler`: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        if (!($response instanceof Response)) {
            $type = get_debug_type($response);
            throw new RuntimeException(
                "Handler `$this->handler` must return a `" . Response::class . "` object but returned `$type`. " .
                ($responseHandler instanceof ResponseHandlerInterface
                    ? "The current ResponseHandler `" . $responseHandler::class . "` is in use."
                    : "Consider using a ResponseHandler for enhanced flexibility."
                ) . " A custom ResponseHandler can be implemented if automatic conversion of `$type` to `" . Response::class . "` is required."
            );
        }

        foreach ($this->middleware as $name => $middleware) {
            if ($middleware instanceof AfterInterface) {
                try {
                    if ($middleware->after($request, $response)) {
                        return $response;
                    }
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "middleware `{$name}->after()` exception: " . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }
        return $response;
    }
}