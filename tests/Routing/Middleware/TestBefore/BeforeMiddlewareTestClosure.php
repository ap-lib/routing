<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing\Middleware\TestBefore;

use AP\Routing\Request\Method;
use AP\Routing\Request\Request;
use AP\Routing\Response\Response;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Routing\Hashmap\Hashmap;
use AP\Routing\Routing\Routing\Hashmap\HashmapIndex;
use AP\Routing\Tests\Helpers\ClosureMiddleware;
use AP\Routing\Tests\Helpers\MakeDefaultRequest;
use PHPUnit\Framework\TestCase;


final class BeforeMiddlewareTestClosure extends TestCase
{
    private const        METHOD         = Method::GET;
    private const string PATH           = '/';
    private const string SOME_GET       = 'someGet';
    private const string MIDDLEWARE_SAY = 'i am middleware';
    private const string HANDLER_SAY    = 'i am handler';

    public function makeRouting()
    {
        $index = new HashmapIndex();
        $index->addEndpoint(
            self::METHOD,
            self::PATH,
            new Endpoint(
                [self::class, "handler"],
                [
                    [self::class, "middleware"]
                ]
            )
        );

        $routing = new Hashmap();
        $routing->init($index->make());

        return $routing;
    }

    public function testIncludeSecretGet()
    {
        $response = $this->makeRouting()->getRoute(self::METHOD, self::PATH)->endpoint->run(
            MakeDefaultRequest::make(
                self::PATH,
                self::METHOD,
                get: [
                    self::SOME_GET => "yes"
                ]
            ),
        );

        $this->assertEquals(
            self::MIDDLEWARE_SAY, $response->body
        );
    }

    public function testNoIncludeSecretGet()
    {
        $response = $this->makeRouting()->getRoute(self::METHOD, self::PATH)->endpoint->run(
            MakeDefaultRequest::make(
                self::PATH,
                self::METHOD,
                get: [
                    //self::SOME_GET => "yes"
                ]
            ),
        );

        $this->assertEquals(
            self::HANDLER_SAY, $response->body
        );
    }

    public static function middleware(): ClosureMiddleware
    {
        return new ClosureMiddleware(
            before: function (Request $request) {
                if (key_exists(self::SOME_GET, $request->get)) {
                    return new Response(self::MIDDLEWARE_SAY);
                }
                return null;
            }
        );
    }

    public static function handler(): Response
    {
        return new Response(self::HANDLER_SAY);
    }
}
