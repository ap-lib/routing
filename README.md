# AP\Routing

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**High-performance, flexible PHP routing library designed for speed and extensibility.**

Routing is optimized for static method calls and relies on a pre-built index for maximum efficiency. It provides simple, direct hashmap-based routing while allowing custom strategies and middleware support.

## Installation

```bash
composer require ap-lib/routing
```

## Features

- 🚀 **Performance-first** - Designed for minimal overhead, using direct static method calls
- ⚡ **Pre-built index** - Requires indexing routes in advance for the fastest possible lookups
- 🔗 **Static hashmap routing** - Default routing mechanism based on an ultra-fast hashmap index
- 🛠 **Extensible architecture** - Supports custom routing strategies through a plugin-like system
- 🔄 **Middleware support** - Easily add middleware for request processing and authentication


## Requirements

- PHP 8.3 or higher

## Getting started

### Base use case

```php
#Handlers must be static methods
class MainController
{
    public static function handlerRoot(Request $request): Response
    {
        return new Response("main page");
    }

    public function handlerHelloName(Request $request): Response
    {
        $name = isset($request->get['name']) && is_string($request->get['name'])
            ? $request->get['name']
            : "guest";

        return new Response("Hello " . htmlspecialchars($name));
    }
}

// make routing
$routing = new Hashmap();

// setup index
$index = $routing->getIndexMaker();

$index->addEndpoint(Method::GET, "/", new Endpoint(
    [MainController::class, "handlerRoot"]
));

$index->addEndpoint(Method::GET, "/hello", new Endpoint(
    [MainController::class, "handlerHelloName"]
));

// init routing
$routing->init($index->make());

 // run `GET /`
try {
    $routingResult = $routing->getRoute(Method::GET, "/");
} catch (NotFound) {
    throw new Exception("ERROR NOT FOUND");
}

// execute handler + optional middlewares
$response = $routingResult->endpoint->run(
    request: new Request(
        method: Method::GET,
        path: "/",
        get: [],
        post: [],
        cookie: [],
        headers: [],
        files: [],
        body: "",
        params: $routingResult->params,
        ip: "127.0.0.1"
    )
);

/*
    AP\Routing\Response\Response Object (
        [body] => main page
        [code] => 200
    )
*/

// run `GET /hello?name=John`
try {
    $routingResult = $routing->getRoute(Method::GET, "/hello");
} catch (NotFound) {
    throw new Exception("ERROR NOT FOUND");
}

$response = $routingResult->endpoint->run(
    request: new Request(
        method: Method::GET,
        path: "/hello",
        get: ["name" => "John"],
        post: [],
        cookie: [],
        headers: [],
        files: [],
        body: "",
        params: $routingResult->params,
        ip: "127.0.0.1"
    )
);

/*
    AP\Routing\Response\Response Object
    (
        [body] => Hello John
        [code] => 200
    )
*/

```