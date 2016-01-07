<?php
namespace Nodes\Api\Http\Middlewares;

use Dingo\Api\Http\Middleware\Auth as DingoHttpMiddlewareAuth;
use Nodes\Api\Auth\Auth as Authentication;
use Nodes\Api\Routing\Router;

/**
 * Class Auth
 *
 * @package Nodes\Api\Http\Middleware
 */
class Auth extends DingoHttpMiddlewareAuth
{
    /**
     * Create a new auth middleware instance
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @param  \Nodes\Api\Routing\Router $router
     * @param  \Nodes\Api\Auth\Auth      $auth
     */
    public function __construct(Router $router, Authentication $auth)
    {
        $this->router = $router;
        $this->auth = $auth;
    }
}