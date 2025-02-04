<?php declare(strict_types=1);

namespace AP\Routing\Routing\Routing\Hashmap;

use AP\Routing\Request\Method;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\DuplicateRoutePath;
use AP\Routing\Routing\Exception\NoAllowedRoutePath;
use AP\Routing\Routing\Routing\IndexInterface;

/**
 * Builds a hashmap-based index for static route matching
 *
 * Supports only exact, static paths with no dynamic parameters or wildcards
 * Ensures route uniqueness and validates allowed path formats
 * Designed for high-speed lookups in a pre-generated routing table
 *
 * @see Hashmap related worker implementation
 */
class HashmapIndexInterface implements IndexInterface
{
    public const string ROUTE_REGEXP = "#^/[a-zA-Z0-9_\-./:@&=+$,;!*'()%]*$#";

    protected array $routes = [];

    /**
     * Adds an endpoint to the index
     *
     * Supports only exact static routes; dynamic routes with parameters or wildcards aren't allowed
     * Ensures that paths conform to the allowed pattern and prevents duplicate conflicting paths
     *
     * @param Method   $method   HTTP method
     * @param string   $path     Exact route path
     * @param Endpoint $endpoint Associated endpoint data
     * @return static
     * @throws DuplicateRoutePath If the same path is registered with a different endpoint
     * @throws NoAllowedRoutePath If the path contains invalid characters or patterns
     */
    public function addEndpoint(Method $method, string $path, Endpoint $endpoint): static
    {
        if (!preg_match(self::ROUTE_REGEXP, $path)) {
            throw new NoAllowedRoutePath();
        }

        if (!isset($this->routes[$method->value])) {
            $this->routes[$method->value] = [];
        }

        if (isset($this->routes[$method->value][$path])) {
            // if duplicate endpoint is same ignore it
            if ($endpoint->serialize() != $this->routes[$method->value][$path]) {
                throw new DuplicateRoutePath();
            }
        } else {
            $this->routes[$method->value][$path] = $endpoint->validateException(true)->serialize();
        }
        return $this;
    }

    /**
     * Returns the generated route index
     *
     * @return array
     */
    public function make(): array
    {
        return $this->routes;
    }
}