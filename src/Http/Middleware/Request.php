<?php
namespace Nodes\Api\Http\Middleware;

use Illuminate\Contracts\Foundation\Application;
use Dingo\Api\Http\Middleware\Request as DingoHttpMiddlewareRequest;
use Dingo\Api\Http\RequestValidator as DingoHttpRequestValidator;
use Nodes\Api\Exceptions\Handler as ExceptionHandler;
use Nodes\Api\Routing\Router;

/**
 * Class Request
 *
 * @package Nodes\Api\Http\Middleware
 */
class Request extends DingoHttpMiddlewareRequest
{
    /**
     * Create a new request middleware instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Nodes\Api\Exceptions\Handler                $exception
     * @param \Nodes\Api\Routing\Router                    $router
     * @param \Dingo\Api\Http\RequestValidator             $validator
     * @param array                                        $middleware
     */
    public function __construct(Application $app, ExceptionHandler $exception, Router $router, DingoHttpRequestValidator $validator, array $middleware)
    {
        $this->app = $app;
        $this->exception = $exception;
        $this->router = $router;
        $this->validator = $validator;
        $this->middleware = $middleware;
    }
}