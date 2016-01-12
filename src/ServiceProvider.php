<?php
namespace Nodes\Api;

use Dingo\Api\Http\Parser\Accept as DingoHttpAcceptParser;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Illuminate\Foundation\AliasLoader;
use Nodes\AbstractServiceProvider as NodesAbstractServiceProvider;
use Nodes\Api\Http\Middlewares\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middlewares\Ratelimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Support\Facades\API;
use Nodes\Api\Support\Facades\Route;
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
     * Indicates if loading of the provider is deferred.
     *
     * @var boolean
     */
    protected $defer = true;

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

        // Register middlewares with router
        $this->app['router']->middleware('api.auth', NodesHttpMiddlewareAuth::class);
        $this->app['router']->middleware('api.throttle', NodesHttpMiddlewareRateLimit::class);

        // Rebind API router
        $this->app->rebinding('api.routes', function ($app, $routes) {
            $app['api.url']->setRouteCollections($routes);
        });

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

        // Register facades
        $this->registerFacades();
    }

    /**
     * registerFacades
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerFacades()
    {
        $this->app->booting(function() {
            $loader = AliasLoader::getInstance();
            $loader->alias('API', API::class);
            $loader->alias('APIRoute', Route::class);
        });
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
     * @access public
     * @return void
     */
    public function installScaffolding()
    {
        // Ask for confirmation before we start the scaffolding
        if (!$this->output->confirm('Do you wish to generate API scaffolding?', true)) {
            return;
        }


    }
}