<?php declare(strict_types=1);

namespace AP\Routing\Request;

/**
 * Enum representing standard HTTP methods.
 *
 * These methods define the type of action performed in an HTTP request.
 * Based on the HTTP/1.1 specification (RFC 7231, RFC 5789).
 *
 * Why CONNECT and TRACE aren't implemented:
 * - `CONNECT`: used for establishing TCP tunnels, for example, HTTPS via proxy, which isn't relevant for PHP web servers.
 *   - It requires low-level socket handling, which should be managed by proxies: Nginx, Apache, HAProxy, rather than the app.
 * - `TRACE`: used for request debugging but is a security risk due to Cross-Site Tracing (XST) attacks.
 *   - Most web servers turn off TRACE by default on Nginx, Apache, Cloudflare.
 *   - Debugging should be done via logging rather than exposing `TRACE` requests.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7231
 * @see https://datatracker.ietf.org/doc/html/rfc5789 (PATCH method)
 */
enum Method: string
{
    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case DELETE  = 'DELETE';
    case PATCH   = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case HEAD    = 'HEAD';
}