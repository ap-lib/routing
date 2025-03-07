<?php declare(strict_types=1);

namespace AP\Routing\Routing\Endpoint\ParseMiddleware;


use AP\Routing\Middleware\AfterInterface;
use AP\Routing\Middleware\BeforeInterface;
use AP\Routing\Routing\Endpoint;
use Generator;

interface ParseMiddlewareInterface
{
    /**
     * @param Endpoint $endpoint
     * @return Generator<AfterInterface|BeforeInterface>
     */
    public function parse(Endpoint $endpoint): Generator;
}