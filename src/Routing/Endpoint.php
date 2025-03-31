<?php declare(strict_types=1);

namespace AP\Routing\Routing;

use AP\Logger\Log;
use AP\Routing\Middleware\AfterInterface;
use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Request\Request;
use AP\Routing\Response\Handler\ResponseHandlerInterface;
use AP\Routing\Response\Response;
use AP\Routing\Routing\Endpoint\ParseMiddleware\ParseMiddlewareInterface;
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
    /**
     * @param string|array $handler The callable handler responsible for processing the request
     *                        It must be a static method reference and can have one of the following signatures:
     *                         `- fn(Request $request): Response|mixed`
     *                         `- fn(): Response|mixed`
     *
     * @param array<string|array> $middleware List of strings referencing static methods, where each method must return
     *                                  an object implementing `BeforeInterface` and/or `AfterInterface`
     *                                  Middleware static methods mustn't have any parameters
     */
    public function __construct(
        readonly public string|array $handler,
        readonly public array        $middleware = [],
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
            try {
                $name = self::convert($this->handler);
            } catch (Throwable) {
                $name = "";
            }
            throw new UnexpectedValueException("Handler `$name` must be callable");
        }

        foreach ($this->middleware as $mv) {
            if (!(is_string($mv) || is_array($mv)) || !is_callable($mv)) {
                throw new UnexpectedValueException("All middleware must be strings or array and callable");
            }
            if ($validateMiddlewareObjects) {
                $mv_res = $mv();
                if (!($mv_res instanceof BeforeInterface) && !($mv_res instanceof AfterInterface)) {
                    $before  = BeforeInterface::class;
                    $after   = AfterInterface::class;
                    $mw_text = self::convert($mv);
                    throw new UnexpectedValueException(
                        "The result of this function `$mw_text`, used as a middleware link, must be an object " .
                        "that implements one of the required interfaces: $before or $after."
                    );
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
     * @return array<BeforeInterface|AfterInterface> The list of middleware instances
     */
    public function getMiddlewareObjects(?ParseMiddlewareInterface $middlewareParser = null): array
    {
        $result = [];
        foreach ($this->middleware as $middleware_callable) {
            $obj = $middleware_callable();
            if (($obj instanceof BeforeInterface) || ($obj instanceof AfterInterface)) {
                $result[] = $obj;
            } else {
                Log::error(
                    "middleware does not implement a required interface",
                    [
                        "middleware" => $middleware_callable
                    ],
                    "ap:routing"
                );
            }
        }

        if ($middlewareParser instanceof ParseMiddlewareInterface) {
            foreach ($middlewareParser->parse($this) as $mv) {
                $result[] = $mv;
            }
        }
        return $result;
    }

    /**
     * convert array/string callable to sting callable
     * with no validation what it is valid callable
     * just checking format if array it is = [string, string]
     *
     * @param array|string $callable
     * @return string
     */
    private static function convert(array|string $callable): string
    {

        if (is_array($callable)) {
            if (
                count($callable) == 2
                && isset($callable[0], $callable[1])
                && is_string($callable[0])
                && is_string($callable[1])
            ) {
                return $callable[0] . "::" . $callable[1];
            }
            throw new RuntimeException("");
        }
        return $callable;
    }

    /**
     * Serializes the endpoint into a string representation with deep validation
     *
     * @return string The serialized representation of the endpoint
     * @throws UnexpectedValueException If the handler isn't callable or if middleware is invalid.
     */
    public function serialize(): string
    {
        $this->validateException(true);

        return implode(
            ",",
            array_merge(
                [self::convert($this->handler)],
                array_map(
                    function ($v) {
                        return self::convert($v);
                    },
                    $this->middleware
                )
            )
        );
    }

    /**
     * Deserializes a string representation into an `Endpoint` instance
     *
     * @param string|array $data The serialized endpoint data
     * @return static The deserialized `Endpoint` instance
     */
    public static function deserialize(string|array $data): static
    {
        $data = is_array($data)
            ? $data
            : explode(",", $data);
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
     * @param ParseMiddlewareInterface|null $middlewareParser Optional parser for additional middlewares, no
     * @return Response The processed response
     *
     * @throws RuntimeException If the handler or middleware produces an invalid response
     */
    public function run(
        Request                   $request,
        ?ResponseHandlerInterface $responseHandler = null,
        ?ParseMiddlewareInterface $middlewareParser = null,
    ): Response
    {
        $middlewares = $this->getMiddlewareObjects($middlewareParser);

        foreach ($middlewares as $middleware) {
            if ($middleware instanceof BeforeInterface) {
                try {
                    $response = $middleware->before($request);
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "middleware `" . $middleware::class . "->before()` exception: " . $e->getMessage(),
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
                "Handler `" . self::convert($this->handler) . "` exception: " . $e->getMessage(),
                0,
                $e
            );
        }

        if ($responseHandler instanceof ResponseHandlerInterface) {
            try {
                $response = $responseHandler->convert($response);
            } catch (Throwable $e) {
                throw new RuntimeException(
                    "invalid handler response `" . self::convert($this->handler) . "`: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        if (!($response instanceof Response)) {
            $type = get_debug_type($response);
            throw new RuntimeException(
                "Handler `" . self::convert($this->handler) . "` must return a `" . Response::class . "` object but returned `$type`. " .
                ($responseHandler instanceof ResponseHandlerInterface
                    ? "The current ResponseHandler `" . $responseHandler::class . "` is in use."
                    : "Consider using a ResponseHandler for enhanced flexibility."
                ) . " A custom ResponseHandler can be implemented if automatic conversion of `$type` to `" . Response::class . "` is required."
            );
        }

        foreach ($middlewares as $middleware) {
            if ($middleware instanceof AfterInterface) {
                try {
                    if ($middleware->after($request, $response)) {
                        return $response;
                    }
                } catch (Throwable $e) {
                    throw new RuntimeException(
                        "middleware `->after()` exception: " . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }
        return $response;
    }
}