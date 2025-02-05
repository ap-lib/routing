<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing\Hashmap;

use AP\Routing\Request\Method;
use AP\Routing\Response\Handler\BaseResponseHandler;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\NotFound;
use AP\Routing\Routing\Routing\Hashmap\Hashmap;
use AP\Routing\Routing\Routing\Hashmap\HashmapIndex;
use AP\Routing\Tests\Helpers\Handlers;
use AP\Routing\Tests\Helpers\MakeDefaultRequest;
use Exception;
use PHPUnit\Framework\TestCase;

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
            $routingResult = $routing->getRoute(Method::GET, self::PATH_ROOT);
        } catch (NotFound) {
            throw new Exception("ERROR NOT FOUND");
        }

        $response = $routingResult->endpoint->run(
            request: MakeDefaultRequest::make(self::PATH_ROOT),
            responseHandler: new BaseResponseHandler() // to convert string response from handler to Response object
        );

        $this->assertEquals(
            Handlers::handlerRoot(),
            $response->body
        );
    }

    // typical errors

    public function testNotFound(): void
    {
        $this->expectException(NotFound::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::GET, "/not-found");
    }

    public function testNotFoundWithThisMethod(): void
    {
        $this->expectException(NotFound::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::POST, self::PATH_ROOT);
    }

    public function testHandlerWithException(): void
    {
        $this->expectException(Exception::class);
        $routing = new Hashmap();
        $routing->init($this->makeBaseIndex());
        $routing->getRoute(Method::GET, self::PATH_EXCEPTION)
            ->endpoint
            ->run(MakeDefaultRequest::make(self::PATH_ROOT));
    }


}
