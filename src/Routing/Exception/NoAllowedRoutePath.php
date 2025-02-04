<?php declare(strict_types=1);

namespace AP\Routing\Routing\Exception;

use AP\Routing\Routing\Routing\IndexInterface;
use Exception;

/**
 * Exception thrown when a route can't be added to a specific routing index
 * @see IndexInterface
 */
class NoAllowedRoutePath extends Exception
{
}