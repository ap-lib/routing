<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing\Hashmap;

use AP\Routing\Request\Method;
use AP\Routing\Request\Request;
use AP\Routing\Response\Handler\BaseResponseHandler;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\NotFound;
use AP\Routing\Routing\Routing\Hashmap\Hashmap;
use AP\Routing\Routing\Routing\Hashmap\HashmapIndex;
use AP\Routing\Tests\Helpers\Handlers;
use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;

final class HashmapTest extends TestCase
{
    const string PATH_ROOT        = "/";
    const string PATH_HELLO_WORLD = "/hello-world";
    const string PATH_EXCEPTION   = "/exception";

    public function makeBaseIndex(): array
    {
        $endpoint_root = new Endpoint(
            [Handlers::class, "handlerRoot"]
        );

        $endpoint_helloWorld = new Endpoint(
            [Handlers::class, "handlerHelloWorld"]
        );

        $endpoint_exception = new Endpoint(
            [Handlers::class, "handlerThrowException"]
        );

        $index = new HashmapIndex();
        $index->addEndpoint(Method::GET, self::PATH_ROOT, $endpoint_root);
        $index->addEndpoint(Method::GET, self::PATH_HELLO_WORLD, $endpoint_helloWorld);
        $index->addEndpoint(Method::POST, self::PATH_HELLO_WORLD, $endpoint_helloWorld);
        $index->addEndpoint(Method::GET, self::PATH_EXCEPTION, $endpoint_exception);

        return $index->make();
    }

    /**
     * @throws Exception
     */
    public function testBasicFlow(): void
    {
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());

        try {
            try {
                $res      = $routing->getRoute(Method::GET, self::PATH_ROOT);
                $response = $res->endpoint->run(
                    request: new Request(
                        method: Method::GET,
                        path: self::PATH_ROOT,
                        get: [],
                        post: [],
                        cookie: [],
                        headers: [],
                        files: [],
                        body: "",
                        params: $res->params
                    ),
                    responseHandler: new BaseResponseHandler() // to convert string response from handler to Response object
                );
                $this->assertEquals(
                    Handlers::handlerRoot(),
                    $response->body
                );
            } catch (NotFound) {
                throw new Exception("ERROR NOT FOUND");
            }
        } catch (Throwable) {
            throw new Exception("OTHER ERROR");
        }
    }

    public function testNotFound(): void
    {
        $this->expectException(NotFound::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::GET, "/not-found");
    }

    public function testHandlerWithException(): void
    {
        $this->expectException(Exception::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::GET, self::PATH_EXCEPTION)
            ->endpoint
            ->run(
                request: new Request(
                    method: Method::GET,
                    path: self::PATH_ROOT,
                    get: [],
                    post: [],
                    cookie: [],
                    headers: [],
                    files: [],
                    body: "",
                    params: []
                ),
                responseHandler: new BaseResponseHandler() // to convert string response from handler to Response object
            );
    }

    public function testHandlerWithException(): void
    {
        $this->expectException(Exception::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::GET, self::PATH_EXCEPTION)
            ->endpoint
            ->run(
                request: new Request(
                    method: Method::GET,
                    path: self::PATH_EXCEPTION,
                    get: [],
                    post: [],
                    cookie: [],
                    headers: [],
                    files: [],
                    body: "",
                    params: []
                ),
                responseHandler: new BaseResponseHandler() // to convert string response from handler to Response object
            );
    }
}
