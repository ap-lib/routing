<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing\Middleware\TestAfter;

use AP\Routing\Request\Method;
use AP\Routing\Response\Response;
use AP\Routing\Routing\Endpoint;
use AP\Routing\Routing\Routing\Hashmap\Hashmap;
use AP\Routing\Routing\Routing\Hashmap\HashmapIndex;
use AP\Routing\Tests\Helpers\MakeDefaultRequest;
use PHPUnit\Framework\TestCase;


final class AfterMiddlewareTestClass extends TestCase
{
    private const        METHOD            = Method::GET;
    private const string PATH              = '/';
    private const string SOME_GET          = 'someGet';
    private const string MIDDLEWARE_APPEND = '; hello handler, i am middleware';
    private const string HANDLER_SAY       = 'i am handler';

    public function makeRouting(array $middleware, int $count = 1)
    {
        $mv = [];
        for ($i = 0; $i < $count; $i++) {
            $mv[] = $middleware;
        }

        $index = new HashmapIndex();
        $index->addEndpoint(
            self::METHOD,
            self::PATH,
            new Endpoint(
                [self::class, "handler"],
                $mv
            )
        );

        $routing = new Hashmap();
        $routing->init($index->make());

        return $routing;
    }

    public static function middlewareReplace(): AfterMiddleware
    {
        return new AfterMiddleware(
            self::SOME_GET,
            self::MIDDLEWARE_APPEND,
            true
        );
    }

    public static function middlewareUpdate(): AfterMiddleware
    {
        return new AfterMiddleware(
            self::SOME_GET,
            self::MIDDLEWARE_APPEND,
            false
        );
    }

    public static function middlewareUpdateExit(): AfterMiddleware
    {
        return new AfterMiddleware(
            self::SOME_GET,
            self::MIDDLEWARE_APPEND,
            false,
            true,
        );
    }

    public static function middlewareReplaceExit(): AfterMiddleware
    {
        return new AfterMiddleware(
            self::SOME_GET,
            self::MIDDLEWARE_APPEND,
            true,
            true,
        );
    }

    public static function handler(): Response
    {
        return new Response(self::HANDLER_SAY);
    }

    public static function makeRequest(bool $include_get)
    {
        return MakeDefaultRequest::make(
            self::PATH,
            self::METHOD,
            get: $include_get
                ? [self::SOME_GET => "yes"]
                : []
        );
    }

    public function testIncludeSecretGetReplace()
    {
        $response = $this->makeRouting([self::class, "middlewareReplace"])
            ->getRoute(self::METHOD, self::PATH)->endpoint
            ->run(self::makeRequest(true));

        $this->assertEquals(
            self::HANDLER_SAY . self::MIDDLEWARE_APPEND,
            $response->body
        );
    }

    public function testIncludeSecretGetUpdate()
    {
        $response = $this->makeRouting([self::class, "middlewareUpdate"])
            ->getRoute(self::METHOD, self::PATH)->endpoint
            ->run(self::makeRequest(true));

        $this->assertEquals(
            self::HANDLER_SAY . self::MIDDLEWARE_APPEND,
            $response->body
        );
    }

    public function testIncludeSecretGetDoubleUpdate()
    {
        $endpoint = $this->makeRouting([self::class, "middlewareUpdate"], count: 2)
            ->getRoute(self::METHOD, self::PATH)->endpoint;

        $response  = $endpoint->run(self::makeRequest(true));

        $this->assertEquals(
            self::HANDLER_SAY . self::MIDDLEWARE_APPEND . self::MIDDLEWARE_APPEND,
            $response->body
        );
    }

    public function testIncludeSecretGetDoubleReplaceButExitAfterFirst()
    {
        $response = $this->makeRouting([self::class, "middlewareReplaceExit"], count: 2)
            ->getRoute(self::METHOD, self::PATH)->endpoint
            ->run(self::makeRequest(true));

        $this->assertEquals(
            self::HANDLER_SAY . self::MIDDLEWARE_APPEND,
            $response->body
        );
    }

    public function testNoIncludeSecretGet()
    {
        $response = $this->makeRouting([self::class, "middlewareReplace"])
            ->getRoute(self::METHOD, self::PATH)->endpoint
            ->run(self::makeRequest(false));

        $this->assertEquals(
            self::HANDLER_SAY,
            $response->body
        );
    }
}
