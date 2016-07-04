<?php
namespace Nodes\Api;

use Dingo\Api\Event\RequestWasMatched as DingoEventRequestWasMatched;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Nodes\Api\Http\Middleware\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Support\Traits\DingoServiceProvider;
use Nodes\Api\Support\Traits\DingoLaravelServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Nodes\Api
 */
class ServiceProvider extends IlluminateServiceProvider
{
    use DingoServiceProvider, DingoLaravelServiceProvider;

    /**
     * Boot the service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Set response static instances
        $this->setResponseStaticInstances();

        // Configure the "Accept"-header parser
        DingoHttpRequest::setAcceptParser($this->app['Dingo\Api\Http\Parser\Accept']);

        // Rebind API router
        $this->app->rebinding('api.routes', function ($app, $routes) {
            $app['api.url']->setRouteCollections($routes);
        });

        // Initiate HTTP kernel
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        /// Add middlewares to HTTP request
        $this->app[NodesHttpMiddlewareRequest::class]->mergeMiddlewares(
            $this->gatherAppMiddleware($kernel)
        );

        // Prepend request middleware
        $this->addRequestMiddlewareToBeginning($kernel);

        // Replace route dispatcher
        $this->app['events']->listen(DingoEventRequestWasMatched::class, function (DingoEventRequestWasMatched $event) {
            $this->replaceRouteDispatcher();
            $this->updateRouterBindings();
        });

        // Load project routes
        $this->loadRoutes();

        // Register namespace for API views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nodes.api');

        // Register publish groups
        $this->publishGroups();
    }

    /**
     * Register the service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function register()
    {
        // Dingo service provider
        $this->registerServiceProvider();

        // Dingo Laravel service provider
        $this->registerLaravelServiceProvider();
    }

    /**
     * Register publish groups
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function publishGroups()
    {
        // Config files
        $this->publishes([
            __DIR__ . '/../config/auth.php'           => config_path('nodes/api/auth.php'),
            __DIR__ . '/../config/errors.php'         => config_path('nodes/api/errors.php'),
            __DIR__ . '/../config/middleware.php'     => config_path('nodes/api/middleware.php'),
            __DIR__ . '/../config/response.php'       => config_path('nodes/api/response.php'),
            __DIR__ . '/../config/settings.php'       => config_path('nodes/api/settings.php'),
            __DIR__ . '/../config/throttling.php'     => config_path('nodes/api/throttling.php'),
            __DIR__ . '/../config/transformer.php'    => config_path('nodes/api/transformer.php')
        ], 'config');
    }

    /**
     * Load project API routes
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access private
     * @return void
     */
    private function loadRoutes()
    {
        // Generate routes directory path
        $routesDirectory = base_path('project/Routes/Api');

        // Make sure our directory exists
        if (!file_exists($routesDirectory)) {
            return;
        }

        // Load routes in directory
        load_directory($routesDirectory);
    }
}
