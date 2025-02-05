<?php declare(strict_types=1);

namespace AP\Routing\Routing\Routing\Hashmap;

use AP\Routing\Request\Method;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\NotFound;
use AP\Routing\Routing\Routing\IndexInterface;
use AP\Routing\Routing\Routing\RoutingInterface;
use AP\Routing\Routing\RoutingResult;

/**
 * Implements a high-performance hashmap-based routing mechanism
 *
 * Uses a pre-generated index to quickly resolve routes
 * This is the fastest routing index, as it allows direct lookups with minimal overhead
 */
class Hashmap implements RoutingInterface
{
    /**
     * Route index mapping HTTP methods and paths to their serialized endpoints
     *
     * @var array<string, string>
     */
    private array $index;

    /**
     * @param array<string,string> $index
     * @see HashmapIndex you must to the prepare index use this class
     */
    public function init(array $index): static
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Retrieves the routing result for a given HTTP method and path
     *
     * Works only for direct paths with no dynamic segments or parameter
     * Paths must be exactly defined in the pre-generated index
     *
     * @param Method $method The HTTP method of the request
     * @param string $path The requested route path
     * @return RoutingResult The result containing the matched route details
     *
     * @throws NotFound If the requested route doesn't exist
     */
    public function getRoute(Method $method, string $path): RoutingResult
    {
        if (isset($this->index[$method->value][$path])) {
            return new RoutingResult(
                Endpoint::deserialize($this->index[$method->value][$path])
            );
        }
        throw new NotFound;
    }

    /**
     * Return related with Hashmap index maker
     *
     * @return HashmapIndex
     */
    public function getIndexMaker(): IndexInterface
    {
        return new HashmapIndex();
    }
}