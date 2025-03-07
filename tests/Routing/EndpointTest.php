<?php declare(strict_types=1);

namespace AP\Routing\Tests\Routing;

use AP\Routing\Routing\Endpoint;
use AP\Routing\Tests\Helpers\GoodMiddleware;
use AP\Routing\Tests\Helpers\Handlers;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

//class GoodMiddleware implements BeforeInterface
//{
//    public function before(Request $request): ?Response
//    {
//        return null;
//    }
//}

final class EndpointTest extends TestCase
{


    // handler tests

    public function testSerializeHandler(): void
    {
        // string version
        $this->assertEquals(
            Handlers::class . "::handlerStaticPublic",
            (new Endpoint(Handlers::class . "::" . "handlerStaticPublic"))->serialize()
        );

        // array version
        $this->assertEquals(
            Handlers::class . "::handlerStaticPublic",
            (new Endpoint([Handlers::class, "handlerStaticPublic"]))->serialize()
        );
    }

    public function testSerializeHandlerProtected(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new Endpoint(Handlers::class . "::handlerStaticProtected"))->serialize();
    }

    public function testSerializeHandlerNonStaticPublic(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new Endpoint([Handlers::class, "handlerNonStaticPublic"]))->serialize();
    }

    // middleware tests

    public function testSerializeMiddleware(): void
    {
        $class = Handlers::class;
        $this->assertEquals(
            "$class::handlerStaticPublic,$class::goodMiddlewareStatic",
            (new Endpoint([Handlers::class, "handlerStaticPublic"], [[Handlers::class, "goodMiddlewareStatic"]]))->serialize()
        );
    }

    public function testSerializeMiddlewareDouble(): void
    {
        $class = Handlers::class;
        $this->assertEquals(
            "$class::handlerStaticPublic,$class::goodMiddlewareStatic,$class::goodMiddlewareStatic",
            (new Endpoint(
                [Handlers::class, "handlerStaticPublic"],
                [
                    [Handlers::class, "goodMiddlewareStatic"],
                    [Handlers::class, "goodMiddlewareStatic"]
                ]
            ))->serialize()
        );
    }

    public function testAttributeMiddleware(): void
    {
        (new Endpoint(
            [Handlers::class, "handlerStaticPublic"],
            [
                [Handlers::class, "goodMiddlewareStatic"],
                [Handlers::class, "goodMiddlewareStatic"]
            ]
        ));
    }
}
