<?php

namespace Nodes\Api\Support\Traits;

use Dingo\Api\Routing\Adapter\Laravel as DingoRoutingLaravelAdapter;
use Illuminate\Contracts\Http\Kernel as IlluminateContractKernel;
use Illuminate\Routing\ControllerDispatcher as IlluminateControllerDispatcher;
use Nodes\Api\Http\Middleware\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middleware\Meta as NodesHttpMiddleware;
use Nodes\Api\Http\Middleware\PrepareController as NodesHttpMiddlewarePrepareController;
use Nodes\Api\Http\Middleware\RateLimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Middleware\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Middleware\UserAgent as NodesHttpMiddlewareUserAgent;
use ReflectionClass;

/**
 * Class DingoLaravelServiceProvider.
 *
 * @trait
 */
trait DingoLaravelServiceProvider
{
    /**
     * Register Dingo's Laravel service provider.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @return void
     */
    protected function registerLaravelServiceProvider()
    {
        // Register router adapter
        $this->registerRouterAdapter();
    }

    /**
     * Replace the route dispatcher.
     *
     * @return void
     */
    protected function replaceRouteDispatcher()
    {
        $this->app->singleton('illuminate.route.dispatcher', function($app) {
            return new IlluminateControllerDispatcher($app['api.router.adapter']->getRouter(), $app);
        });
    }

    /**
     * Grab the bindings from the Laravel router and set them on the adapters
     * router.
     *
     * @return void
     */
    protected function updateRouterBindings()
    {
        foreach ($this->getRouterBindings() as $key => $binding) {
            $this->app['api.router.adapter']->getRouter()->bind($key, $binding);
        }
    }

    /**
     * Get the Laravel routers bindings.
     *
     * @return array
     */
    protected function getRouterBindings()
    {
        $property = (new ReflectionClass($this->app['router']))->getProperty('binders');
        $property->setAccessible(true);

        return $property->getValue($this->app['router']);
    }

    /**
     * Register the router adapter.
     *
     * @return void
     */
    protected function registerRouterAdapter()
    {
        $this->app->singleton('api.router.adapter', function($app) {
            return new DingoRoutingLaravelAdapter($this->cloneLaravelRouter(), $app['router']->getRoutes());
        });
    }

    /**
     * Clone the Laravel router and set the middleware on the cloned router.
     *
     * @return \Illuminate\Routing\Router
     */
    protected function cloneLaravelRouter()
    {
        $router = clone $this->app['router'];
        $router->aliasMiddleware('api.auth', NodesHttpMiddlewareAuth::class);
        $router->aliasMiddleware('api.controllers', NodesHttpMiddlewarePrepareController::class);
        $router->aliasMiddleware('api.throttle', NodesHttpMiddlewareRateLimit::class);
        $router->aliasMiddleware('api.useragent', NodesHttpMiddlewareUserAgent::class);
        $router->aliasMiddleware('api.meta', NodesHttpMiddleware::class);

        return $router;
    }

    /**
     * Add the request middleware to the beginning of the kernel.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return void
     */
    protected function addRequestMiddlewareToBeginning(IlluminateContractKernel $kernel)
    {
        $kernel->prependMiddleware(NodesHttpMiddlewareRequest::class);

    }

    /**
     * Gather the application middleware besides this one so that we can send
     * our request through them, exactly how the developer wanted.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return array
     */
    protected function gatherAppMiddleware(IlluminateContractKernel $kernel)
    {
        $property = (new ReflectionClass($kernel))->getProperty('middleware');
        $property->setAccessible(true);

        return $property->getValue($kernel);
    }
}
