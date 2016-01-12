<?php
namespace Nodes\Api\Support\Traits;

use RuntimeException;
use Dingo\Api\Contract\Debug\ExceptionHandler as DingoContractDebugExceptionHandler;
use Dingo\Api\Contract\Http\Request as DingoContractHttpRequest;
use Dingo\Api\Contract\Routing\Adapter as DingoContractRoutingAdapter;
use Dingo\Api\Console\Command\Cache as DingoConsoleCommandCache;
use Dingo\Api\Console\Command\Docs as DingoConsoleCommandDocs;
use Dingo\Api\Console\Command\Routes as DingoConsoleCommandRoutes;
use Dingo\Api\Dispatcher as DingoDispatcher;
use Dingo\Api\Http\Parser\Accept as DingoHttpAcceptParser;
use Dingo\Api\Http\RateLimit\Handler as DingoRateLimitHandler;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Dingo\Api\Http\RequestValidator as DingoHttpRequestValidator;
use Dingo\Api\Routing\UrlGenerator as DingoRoutingUrlGenerator;
use Dingo\Api\Http\Validation\Accept as DingoHttpValidatorAccept;
use Dingo\Api\Http\Validation\Domain as DingoHttpValidatorDomain;
use Dingo\Api\Http\Validation\Prefix as DingoHttpValidatorPrefix;
use Dingo\Api\Transformer\Factory as DingoTransformerFactory;
use Nodes\Api\Auth\Auth as NodesAuth;
use Nodes\Api\Exceptions\Handler as NodesExceptionHandler;
use Nodes\Api\Http\Middlewares\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middlewares\Ratelimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Middlewares\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Http\Response\Factory as NodesHttpResponseFactory;
use Nodes\Api\Routing\Router as NodesRoutingRouter;
use Nodes\Api\Routing\ResourceRegistrar as NodesRoutingResourceRegistrar;

/**
 * Class DingoApiServiceProvider
 *
 * @trait
 * @package Nodes\Api\Support\Traits
 */
trait DingoApiServiceProvider
{
    /**
     * Register Dingo's API service provider
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function registerApiServiceProvider()
    {
        // Setup class aliases
        $this->setupClassAliases();

        // Register class bindings
        $this->registerExceptionHandler();
        $this->registerDispatcher();
        $this->registerAuth();
        $this->registerRateLimiting();
        $this->registerRouter();
        $this->registerUrlGenerator();
        $this->registerHttpValidation();
        $this->registerResponseFactory();
        $this->registerMiddleware();
        $this->registerTransformer();
        $this->registerDocsCommand();

        // Register console command
        $this->commands([
            DingoConsoleCommandDocs::class,
        ]);

        if (class_exists(\Illuminate\Foundation\Application::class, false)) {
            $this->commands([
                DingoConsoleCommandCache::class,
                DingoConsoleCommandRoutes::class,
            ]);
        }
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
            throw new RuntimeException(sprintf('Unable to boot [%s], configure an API domain or prefix.', 'Nodes\Api\ServiceProvider'));
        }
    }

    /**
     * Setup the class aliases
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
     * @return void
     */
    protected function setupClassAliases()
    {
        $this->app->alias(DingoHttpRequest::class, DingoContractHttpRequest::class);

        $aliases = [
            'api.dispatcher'     => DingoDispatcher::class,
            'api.http.validator' => DingoHttpRequestValidator::class,
            'api.http.response'  => NodesHttpResponseFactory::class,
            'api.router'         => NodesRoutingRouter::class,
            'api.router.adapter' => DingoContractRoutingAdapter::class,
            'api.auth'           => NodesAuth::class,
            'api.limiting'       => DingoRateLimitHandler::class,
            'api.transformer'    => DingoTransformerFactory::class,
            'api.url'            => DingoRoutingUrlGenerator::class,
            'api.exception'      => [
                NodesExceptionHandler::class,
                DingoContractDebugExceptionHandler::class
            ],
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ((array) $aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }
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
            return new NodesExceptionHandler($app['log'], config('nodes.api.errorFormat'), config('nodes.api.debug'));
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
            return new NodesAuth($app['api.router'], $app, $this->prepareConfigValues(config('nodes.api.auth.providers')));
        });
    }

    /**
     * Register the rate limiting
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
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
            $router = new NodesRoutingRouter(
                $app['api.router.adapter'],
                $app['api.exception'],
                $app,
                config('nodes.api.domain'),
                config('nodes.api.prefix')
            );

            $router->setConditionalRequest(config('nodes.api.conditionalRequest'));
            return $router;
        });

        $this->app->singleton(NodesRoutingResourceRegistrar::class, function ($app) {
            return new NodesRoutingResourceRegistrar($app['api.router']);
        });
    }

    /**
     * Register the URL generator
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @access protected
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
            return new NodesHttpResponseFactory($app['api.transformer']);
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
}