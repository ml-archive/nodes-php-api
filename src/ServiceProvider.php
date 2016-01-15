<?php
namespace Nodes\Api;

use Dingo\Api\Http\Parser\Accept as DingoHttpAcceptParser;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Nodes\AbstractServiceProvider as NodesAbstractServiceProvider;
use Nodes\Api\Http\Middleware\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middleware\Ratelimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Support\Traits\DingoApiServiceProvider;
use Nodes\Api\Support\Traits\DingoLaravelServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Nodes\Api
 */
class ServiceProvider extends NodesAbstractServiceProvider
{
    use DingoApiServiceProvider, DingoLaravelServiceProvider;

    /**
     * Package name
     *
     * @var string
     */
    protected $package = 'api';

    /**
     * Facades to install
     *
     * @var array
     */
    protected $facades = [
        'NodesAPI' => \Nodes\Api\Support\Facades\API::class,
        'NodesAPIRoute' => \Nodes\Api\Support\Facades\Route::class
    ];

    /**
     * Register Artisan commands
     *
     * @var array
     */
    protected $commands = [
        \Nodes\Api\Console\Commands\Scaffolding::class
    ];

    /**
     * Array of configs to copy
     *
     * @var array
     */
    protected $configs = [
        'config/api.php' => 'nodes/api.php'
    ];

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
        $this->setupConfig();

        // Set response formatter, transformer and evnet dispatcher
        NodesHttpResponse::setFormatters(prepare_config_instances(config('nodes.api.formats')));
        NodesHttpResponse::setTransformer($this->app['api.transformer']);
        NodesHttpResponse::setEventDispatcher($this->app['events']);

        // Configure the "Accept"-header parser
        DingoHttpRequest::setAcceptParser(
            new DingoHttpAcceptParser(
                config('nodes.api.standardsTree'),
                config('nodes.api.subtype'),
                config('nodes.api.version'),
                config('nodes.api.defaultFormat')
            )
        );

        // Rebind API router
        $this->app->rebinding('api.routes', function ($app, $routes) {
            $app['api.url']->setRouteCollections($routes);
        });

        // Register middlewares with router
        $this->app['router']->middleware('api.auth', NodesHttpMiddlewareAuth::class);
        $this->app['router']->middleware('api.throttle', NodesHttpMiddlewareRateLimit::class);

        // Load project routes
        $this->loadRoutes();
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
        parent::register();

        // Dingo API service provider
        $this->registerApiServiceProvider();

        // Dingo Laravel service provider
        $this->registerLaravelServiceProvider();
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
        $routesDirectory = base_path('project/Routes/');

        // Make sure our directory exists
        if (!file_exists($routesDirectory)) {
            return;
        }

        // Load routes in directory
        load_directory($routesDirectory);
    }

    /**
     * Install scaffolding
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function installScaffolding()
    {
        if (env('NODES_ENV', false)) {
            $this->getOutput()->block([
                'To install Nodes Scaffolding, run the command:',
                'php artisan nodes:api:scaffold'
            ], 'TIP!', 'fg=white;bg=black', ' ', true);
        }
    }
}