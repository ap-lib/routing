<?php declare(strict_types=1);

namespace AP\Routing\Routing\Exception;

use Exception;

/**
 * Exception thrown when a requested route or resource isn't found
 *
 * This typically corresponds to an HTTP 404 error and indicates that no matching
 * route exists in the routing index.
 */
class NotFound extends Exception
{
}