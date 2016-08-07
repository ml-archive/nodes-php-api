<?php

namespace Nodes\Api\Http\Middleware;

use Dingo\Api\Http\Middleware\PrepareController as DingoHttpMiddlewarePrepareController;
use Nodes\Api\Routing\Router;

/**
 * Class PrepareController.
 */
class PrepareController extends DingoHttpMiddlewarePrepareController
{
    /**
     * Create a new prepare controller instance.
     *
     * @param  \Nodes\Api\Routing\Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
