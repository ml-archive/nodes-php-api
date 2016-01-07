<?php
namespace Nodes\Api;

use RuntimeException;
use Dingo\Api\Console\Command\Docs as DingoConsoleCommandDocs;
use Dingo\Api\Dispatcher as DingoDispatcher;
use Dingo\Api\Http\Parser\Accept as DingoHttpAcceptParser;
use Dingo\Api\Http\RateLimit\Handler as DingoRateLimitHandler;
use Dingo\Api\Http\RequestValidator as DingoHttpRequestValidator;
use Dingo\Api\Http\Validation\Accept as DingoHttpValidatorAccept;
use Dingo\Api\Http\Validation\Domain as DingoHttpValidatorDomain;
use Dingo\Api\Http\Validation\Prefix as DingoHttpValidatorPrefix;
use Dingo\Api\Provider\LaravelServiceProvider as DingoLaravelServiceProvider;
use Dingo\Api\Transformer\Factory as DingoTransformerFactory;
use Illuminate\Contracts\Http\Kernel as IlluminateContractKernel;
use Illuminate\Foundation\AliasLoader;
use Nodes\Api\Auth\Auth;
use Nodes\Api\Exceptions\Handler;
use Nodes\Api\Http\Middlewares\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middlewares\Ratelimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Middlewares\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response;
use Nodes\Api\Http\Response\Factory;
use Nodes\Api\Routing\Router;
use Nodes\Api\Routing\ResourceRegistrar;
use Nodes\Api\Support\Facades\API;
use Nodes\Api\Support\Facades\Route;

/**
 * Class ServiceProvider
 *
 * @package Nodes\Api
 */
class ServiceProvider extends DingoLaravelServiceProvider
{
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
        Response::setFormatters(prepare_config_instances(config('nodes.api.formats')));
        Response::setTransformer($this->app['api.transformer']);
        Response::setEventDispatcher($this->app['events']);

        // Register middlewares with router
        $this->app['router']->middleware('api.auth', 'Nodes\Api\Http\Middleware\Auth');
        $this->app['router']->middleware('api.throttle', 'Dingo\Api\Http\Middleware\RateLimit');

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

        // Register facades
        $this->registerFacades();
    }

    /**
     * Setup the configuration
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function setupConfig()
    {
        // Merge package config with project version
        $this->mergeConfigFrom(realpath(__DIR__. '/../config/api.php'), 'nodes.api');

        if (! $this->app->runningInConsole() && empty(config('nodes.api.prefix')) && empty(config('nodes.api.domain'))) {
            throw new RuntimeException('Unable to boot ApiServiceProvider, configure an API domain or prefix.');
        }
    }

    /**
     * Add the request middleware to the beginning of the kernel
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     * @return void
     */
    protected function addRequestMiddlewareToBeginning(IlluminateContractKernel $kernel)
    {
        $kernel->prependMiddleware('Nodes\Api\Http\Middleware\Request');
    }

    /**
     * Register the exception handler
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerExceptionHandler()
    {
        $this->app->singleton('api.exception', function ($app) {
            return new Handler($app['log'], config('nodes.api.errorFormat'), config('nodes.api.debug'));
        });
    }

    /**
     * Register the internal dispatcher
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    public function registerDispatcher()
    {
        $this->app->singleton('api.dispatcher', function ($app) {
            $dispatcher = new DingoDispatcher($app, $app['files'], $app['api.router'], $app['api.auth']);
            $dispatcher->setSubtype(config('nodes.api.subtype'));
            $dispatcher->setStandardsTree(config('nodes.api.standardsTree'));
            $dispatcher->setPrefix(config('nodes.api.prefix'));
            $dispatcher->setDefaultVersion(config('nodes.api.version'));
            $dispatcher->setDefaultDomain(config('nodes.api.domain'));
            $dispatcher->setDefaultFormat(config('nodes.api.defaultFormat'));
            return $dispatcher;
        });
    }

    /**
     * Register the authenticator
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerAuth()
    {
        $this->app->singleton('api.auth', function ($app) {
            return new Auth($app['api.router'], $app, $this->prepareConfigValues(config('nodes.api.auth.providers')));
        });
    }

    /**
     * Register the rate limiting.
     *
     * @return void
     */
    protected function registerRateLimiting()
    {
        $this->app->singleton('api.limiting', function ($app) {
            return new DingoRateLimitHandler($app, $app['cache'], $this->prepareConfigValues(config('nodes.api.throttling')));
        });
    }

    /**
     * Register the router
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('api.router', function ($app) {
            $router = new Router(
                $app['api.router.adapter'],
                new DingoHttpAcceptParser(config('nodes.api.standardsTree'), config('nodes.api.subtype'), config('nodes.api.version'), config('nodes.api.defaultFormat')),
                $app['api.exception'],
                $app,
                config('nodes.api.domain'),
                config('nodes.api.prefix')
            );

            $router->setConditionalRequest(config('nodes.api.conditionalRequest'));
            return $router;
        });

        $this->app->singleton('Nodes\Api\Routing\ResourceRegistrar', function ($app) {
            return new ResourceRegistrar($app['api.router']);
        });
    }

    /**
     * Register the HTTP validation
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerHttpValidation()
    {
        $this->app->singleton('api.http.validator', function ($app) {
            return new DingoHttpRequestValidator($app);
        });

        $this->app->singleton('Dingo\Api\Http\Validation\Domain', function ($app) {
            return new DingoHttpValidatorDomain(config('nodes.api.domain'));
        });

        $this->app->singleton('Dingo\Api\Http\Validation\Prefix', function ($app) {
            return new DingoHttpValidatorPrefix(config('nodes.api.prefix'));
        });

        $this->app->singleton('Dingo\Api\Http\Validation\Accept', function ($app) {
            return new DingoHttpValidatorAccept(
                new DingoHttpAcceptParser(config('nodes.api.standardsTree'), config('nodes.api.subtype'), config('nodes.api.version'), config('nodes.api.defaultFormat')),
                config('nodes.api.strict')
            );
        });
    }

    /**
     * Register the response factory
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerResponseFactory()
    {
        $this->app->singleton('api.http.response', function ($app) {
            return new Factory($app['api.transformer']);
        });
    }

    /**
     * Register the middleware
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerMiddleware()
    {
        $this->app->singleton('Nodes\Api\Http\Middleware\Auth', function ($app) {
            return new NodesHttpMiddlewareAuth($app['api.router'], $app['api.auth']);
        });

        $this->app->singleton('Nodes\Api\Http\Middleware\Request', function ($app) {
            return new NodesHttpMiddlewareRequest($app, $app['api.exception'], $app['api.router'], $app['api.http.validator'], $app['app.middleware']);
        });

        $this->app->singleton('Nodes\Api\Http\Middleware\RateLimit', function ($app) {
            return new NodesHttpMiddlewareRateLimit($app['api.router'], $app['api.limiting']);
        });
    }

    /**
     * Register the transformation layer
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerTransformer()
    {
        $this->app->singleton('api.transformer', function ($app) {
            return new DingoTransformerFactory($app, prepare_config_instance(config('nodes.api.transformer.adapter')));
        });
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
     * Register the documentation command
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access public
     * @return void
     */
    protected function registerDocsCommand()
    {
        $this->app->singleton('Dingo\Api\Console\Command\Docs', function ($app) {
            return new DingoConsoleCommandDocs(
                $app['api.router'],
                $app['Dingo\Blueprint\Blueprint'],
                $app['Dingo\Blueprint\Writer'],
                config('nodes.api.name'),
                config('nodes.api.version')
            );
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
}