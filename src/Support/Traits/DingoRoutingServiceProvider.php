<?php

namespace Nodes\Api\Support\Traits;

use Dingo\Api\Routing\UrlGenerator as DingoRoutingUrlGenerator;
use Nodes\Api\Routing\Router as NodesRoutingRouter;
use Nodes\Api\Routing\ResourceRegistrar as NodesRoutingResourceRegistrar;

/**
 * Class DingoRoutingServiceProvider.
 *
 * @trait
 */
trait DingoRoutingServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerRoutingServiceProvider()
    {
        $this->registerRouter();
        $this->registerUrlGenerator();
    }

    /**
     * Register the router.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('api.router', function ($app) {
            $router = new NodesRoutingRouter(
                $app['api.router.adapter'],
                $app['api.exception'],
                $app,
                config('nodes.api.settings.domain', 'nodes.dk'),
                config('nodes.api.settings.prefix', null)
            );

            $router->setConditionalRequest(config('nodes.api.settings.conditionalRequest'));

            return $router;
        });

        $this->app->singleton(NodesRoutingResourceRegistrar::class, function ($app) {
            return new NodesRoutingResourceRegistrar($app['api.router']);
        });
    }

    /**
     * Register the URL generator.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('api.url', function ($app) {
            $url = new DingoRoutingUrlGenerator($app['request']);
            $url->setRouteCollections($app['api.router']->getRoutes());

            return $url;
        });
    }
}
