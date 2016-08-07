<?php

namespace Nodes\Api\Http\Middleware;

use Dingo\Api\Http\Middleware\RateLimit as DingoHttpMiddlewareRateLimit;
use Dingo\Api\Http\RateLimit\Handler as DingoRateLimitHandler;
use Nodes\Api\Routing\Router;

/**
 * Class Ratelimit.
 */
class RateLimit extends DingoHttpMiddlewareRateLimit
{
    /**
     * Create a new rate limit middleware instance.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param \Nodes\Api\Routing\Router         $router
     * @param \Dingo\Api\Http\RateLimit\Handler $handler
     */
    public function __construct(Router $router, DingoRateLimitHandler $handler)
    {
        $this->router = $router;
        $this->handler = $handler;
    }
}
