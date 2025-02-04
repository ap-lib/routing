<?php declare(strict_types=1);

namespace AP\Routing\Routing\Routing;

use AP\Routing\Request\Method;
use AP\Routing\Routing\Exception\NotFound;
use AP\Routing\Routing\RoutingResult;

/**
 * Interface for managing route resolution and indexing.
 *
 * Implementations of this interface handle route initialization,
 * lookup, and provide access to the indexing mechanism.
 */
interface RoutingInterface
{
    /**
     * Initializes the routing system with a pre-generated index.
     *
     * @param array $index The routing index data.
     * @return static
     */
    public function init(array $index): static;

    /**
     * Retrieves the routing result for a given HTTP method and path
     *
     * If no matching route is found, a `NotFound` exception is thrown
     *
     * @param Method $method The HTTP method of the request
     * @param string $path The requested route path
     * @return RoutingResult The result containing the matched route details
     *
     * @throws NotFound If the requested route doesn't exist
     */
    public function getRoute(Method $method, string $path): RoutingResult;

    /**
     * Returns the index maker used for route storage.
     *
     * @return IndexInterface The route indexing mechanism.
     */
    public function getIndexMaker(): IndexInterface;
}