<?php declare(strict_types=1);

namespace AP\Routing\Tests\Readme\BaseUseCase;

use AP\Routing\Request\Method;
use AP\Routing\Request\Request;
use AP\Routing\Response\Response;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Exception\NotFound;
use AP\Routing\Routing\Routing\Hashmap\Hashmap;
use Exception;
use PHPUnit\Framework\TestCase;

class MainController
{
    public static function handlerRoot(Request $request): Response
    {
        return new Response("main page");
    }

    public static function handlerHelloName(Request $request): Response
    {
        $name = isset($request->get['name']) && is_string($request->get['name'])
            ? $request->get['name']
            : "guest";

        return new Response("Hello " . htmlspecialchars($name));
    }
}

final class BaseUseCaseTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testBasicFlow(): void
    {
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
            )
        );

        $this->assertEquals("main page", $response->body);

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
            )
        );

        $this->assertEquals("Hello John", $response->body);
    }

}
