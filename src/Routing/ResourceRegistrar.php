<?php

namespace Nodes\Api\Routing;

use Dingo\Api\Routing\ResourceRegistrar as DingoRoutingResourceRegistrar;

/**
 * Class ResourceRegistrar.
 */
class ResourceRegistrar extends DingoRoutingResourceRegistrar
{
    /**
     * Create a new resource registrar instance.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param \Nodes\Api\Routing\Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
}
