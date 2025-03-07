<?php declare(strict_types=1);

namespace AP\Routing\Routing\Endpoint\ParseMiddleware;


use AP\Logger\Log;
use AP\Routing\Middleware\AfterInterface;
use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Routing\Endpoint;
use Generator;
use ReflectionException;

class ByMethodAttributes implements ParseMiddlewareInterface
{
    /**
     * @param Endpoint $endpoint
     * @return Generator<AfterInterface|BeforeInterface>
     */
    public function parse(Endpoint $endpoint): Generator
    {
        $handler = is_string($endpoint->handler)
            ? explode("::", $endpoint->handler)
            : $endpoint->handler;

        if (isset($handler[0], $handler[1])) {
            try {
                $reflectionMethod = new \ReflectionMethod($handler[0], $handler[1]);
                $attributes       = $reflectionMethod->getAttributes();
                foreach ($attributes as $attribute) {
                    if (
                        is_subclass_of($attribute->getName(), AfterInterface::class)
                        || is_subclass_of($attribute->getName(), BeforeInterface::class)
                    ) {
                        yield $attribute->newInstance();
                    }
                }
            } catch (ReflectionException $e) {
                Log::error(
                    "handler reflection exception",
                    [
                        "handler"   => $endpoint->handler,
                        "exception" => $e,
                    ],
                    "ap:routing"
                );
            }
        }
    }
}