<?php declare(strict_types=1);

namespace AP\Routing\Routing\Routing;

use AP\Routing\Request\Method;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\DuplicateRoutePath;
use AP\Routing\Routing\Exception\NoAllowedRoutePath;

/**
 * Interface for managing and organizing indexed route storage
 *
 * Implementations of this interface allow adding endpoints and generating
 * a structured routing index that can be used for request handling
 */
interface IndexInterface
{
    /**
     * Adds an endpoint to the routing index.
     *
     * This method registers a route for a specific HTTP method and associates it with an endpoint
     * If a route with the same method and path already exists, a `DuplicateRoutePath` exception is thrown
     *
     * @param Method $method The HTTP method associated with the endpoint
     * @param string $path The route path to be registered.
     * @param Endpoint $endpoint The endpoint handler associated with the route.
     * @return static
     *
     * @throws DuplicateRoutePath If the same method and path already exist in the index
     * @throws NoAllowedRoutePath If the route can't be stored in this index
     */
    public function addEndpoint(Method $method, string $path, Endpoint $endpoint): static;

    /**
     * Generates the final routing index structure.
     *
     * This method compiles all registered routes into a usable format for request handling.
     * The output structure depends on the specific indexing strategy used by the implementation.
     *
     * @return array the structured routing index.
     */
    public function make(): array;
}