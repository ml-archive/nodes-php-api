<?php
namespace Nodes\Api\Http\Middleware;

use Dingo\Api\Http\Middleware\PrepareController as DingoHttpMiddlewarePrepareController;
use Nodes\Api\Routing\Router;

/**
 * Class PrepareController
 *
 * @package Nodes\Api\Http\Middleware
 */
class PrepareController extends DingoHttpMiddlewarePrepareController
{
    /**
     * Create a new prepare controller instance
     *
     * @access public
     * @param  \Nodes\Api\Routing\Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}