<?php declare(strict_types=1);

namespace AP\Routing\Tests\Helpers;

use AP\Context\Context;
use AP\Routing\Request\Method;
use AP\Routing\Request\Request;

class MakeDefaultRequest
{
    public static function make(
        string  $path,
        Method  $method = Method::GET,
        array   $get = [],
        array   $post = [],
        array   $cookie = [],
        array   $headers = [],
        array   $files = [],
        string  $body = "",
        array   $params = [],
        string  $ip = "127.0.0.1",
        Context $context = new Context()
    ): Request
    {
        return new Request(
            method: $method,
            path: $path,
            get: $get,
            post: $post,
            cookie: $cookie,
            headers: $headers,
            files: $files,
            body: $body,
            params: $params,
            ip: $ip,
            context: $context,
        );
    }
}