<?php declare(strict_types=1);

namespace AP\Routing\Tests\Helpers;

use Exception;
use PHPUnit\Event\Code\Throwable;

class Handlers
{
    const string RESULT_ROOT              = 'root';
    const string RESULT_HELLO_WORLD       = 'hello-world';
    const string RESULT_STATIC_PUBLIC     = 'static_public';
    const string RESULT_STATIC_PROTECTED  = 'static_protected';
    const string RESULT_NON_STATIC_PUBLIC = 'non_static_public';

    public static function handlerStaticPublic(): string
    {
        return self::RESULT_STATIC_PUBLIC;
    }

    public function handlerNonStaticPublic(): string
    {
        return self::RESULT_NON_STATIC_PUBLIC;
    }

    public function handlerStaticProtected(): string
    {
        return self::RESULT_STATIC_PROTECTED;
    }

    public static function goodMiddlewareStatic(): GoodMiddleware
    {
        return new GoodMiddleware();
    }

    public static function handlerRoot(): string
    {
        return self::RESULT_ROOT;
    }

    public static function handlerHelloWorld(): string
    {
        return self::RESULT_HELLO_WORLD;
    }

    /**
     * @throws Exception
     */
    public static function handlerThrowException(): string
    {
        throw new Exception();
    }
}