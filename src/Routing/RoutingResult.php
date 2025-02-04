<?php declare(strict_types=1);

namespace AP\Routing\Routing;

/**
 * Represents the result of a route lookup.
 *
 * This class contains the matched endpoint and any extracted route parameters.
 */
readonly class RoutingResult
{
    /**
     * @param Endpoint $endpoint The matched endpoint for the route.
     * @param array<string, string> $params The extracted route parameters.
     */
    public function __construct(
        public Endpoint $endpoint,
        public array    $params = [],
    )
    {
    }
}