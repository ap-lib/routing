<?php declare(strict_types=1);

namespace AP\Routing\Routing\Exception;

use Exception;

/**
 * Exception thrown when a duplicate route path is detected
 *
 * This occurs when multiple routes are registered with the same path,
 * which can lead to unexpected behavior in the routing system
 */
class DuplicateRoutePath extends Exception
{
}