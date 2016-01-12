<?php
namespace Nodes\Api\Support\Traits;

use ReflectionClass;
use Dingo\Api\Routing\Adapter\Laravel as DingoRoutingLaravelAdapter;
use Illuminate\Contracts\Http\Kernel as IlluminateContractKernel;
use Nodes\Api\Http\Middlewares\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response as NodesHttpResponse;

/**
 * Class DingoLaravelServiceProvider
 *
 * @trait
 * @package Nodes\Api\Support\Traits
 */
trait DingoLaravelServiceProvider
{
    /**
     * Register Dingo's Laravel service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerLaravelServiceProvider()
    {
        // Instantiate HTTP kernel
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        // Setup HTTP middlewares
        $this->app->instance('app.middleware', $this->gatherAppMiddleware($kernel));
        $this->addRequestMiddlewareToBeginning($kernel);

        // Register Laravel route adapter
        $this->app->singleton('api.router.adapter', function ($app) {
            return new DingoRoutingLaravelAdapter($app['router']);
        });
    }

    /**
     * Add the request middleware to the beginning of the kernel
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  \Illuminate\Contracts\Http\Kernel $kernel
     * @return void
     */
    protected function addRequestMiddlewareToBeginning(IlluminateContractKernel $kernel)
    {
        $kernel->prependMiddleware(NodesHttpMiddlewareRequest::class);
    }

    /**
     * Gather the application middleware besides this one so that we can send
     * our request through them, exactly how the developer wanted
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param  \Illuminate\Contracts\Http\Kernel $kernel
     * @return array
     */
    protected function gatherAppMiddleware(IlluminateContractKernel $kernel)
    {
        $reflection = new ReflectionClass($kernel);

        $property = $reflection->getProperty('middleware');
        $property->setAccessible(true);

        $middleware = $property->getValue($kernel);

        return $middleware;
    }
}