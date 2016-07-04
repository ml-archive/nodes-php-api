<?php
namespace Nodes\Api\Support\Traits;

use RuntimeException;
use Dingo\Api\Auth\Auth as DingoAuth;
use Dingo\Api\Contract\Debug\ExceptionHandler as DingoContractDebugExceptionHandler;
use Dingo\Api\Contract\Http\Request as DingoContractHttpRequest;
use Dingo\Api\Contract\Routing\Adapter as DingoContractRoutingAdapter;
use Dingo\Api\Dispatcher as DingoDispatcher;
use Dingo\Api\Exception\Handler as DingoExceptionHandler;
use Dingo\Api\Http\RateLimit\Handler as DingoRateLimitHandler;
use Dingo\Api\Http\Request as DingoHttpRequest;
use Dingo\Api\Http\RequestValidator as DingoHttpRequestValidator;
use Dingo\Api\Http\Response\Factory as DingoHttpResponseFactory;
use Dingo\Api\Routing\Router as DingoRoutingRouter;
use Dingo\Api\Routing\UrlGenerator as DingoRoutingUrlGenerator;
use Dingo\Api\Transformer\Factory as DingoTransformerFactory;
use Nodes\Api\Auth\Auth as NodesAuth;
use Nodes\Api\Console\Commands\Cache as NodesConsoleCommandCache;
use Nodes\Api\Console\Commands\Docs as NodesConsoleCommandDocs;
use Nodes\Api\Console\Commands\Routes as NodesConsoleCommandRoutes;
use Nodes\Api\Exceptions\Handler as NodesExceptionHandler;
use Nodes\Api\Http\Response as NodesHttpResponse;

trait DingoServiceProvider
{
    use DingoHttpServiceProvider, DingoRoutingServiceProvider;

    /**
     * Set response static instances.
     *
     * @return void
     */
    protected function setResponseStaticInstances()
    {
        NodesHttpResponse::setFormatters(prepare_config_instances(config('nodes.api.response.formats')));
        NodesHttpResponse::setTransformer($this->app['api.transformer']);
        NodesHttpResponse::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerServiceProvider()
    {
        $this->registerConfig();
        $this->registerClassAliases();
        $this->registerHttpServiceProvider();
        $this->registerRoutingServiceProvider();
        $this->registerExceptionHandler();
        $this->registerDispatcher();
        $this->registerAuth();
        $this->registerTransformer();
        $this->registerDocsCommand();

        if (class_exists(\Illuminate\Foundation\Application::class, false)) {
            $this->commands([
                NodesConsoleCommandCache::class,
                NodesConsoleCommandRoutes::class,
            ]);
        }
    }

    /**
     * Register the configuration.
     *
     * @return void
     */
    protected function registerConfig()
    {
        // Merge project config and default package config
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/auth.php'), 'nodes.api.auth');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/errors.php'), 'nodes.api.errors');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/middleware.php'), 'nodes.api.middleware');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/response.php'), 'nodes.api.response');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/settings.php'), 'nodes.api.settings');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/throttling.php'), 'nodes.api.throttling');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../../config/transformer.php'), 'nodes.api.transformer');

        if (!$this->app->runningInConsole() && empty(config('nodes.api.settings.prefix')) && empty(config('nodes.api.settings.domain'))) {
            throw new RuntimeException(sprintf('Unable to boot [%s], configure an API domain or prefix.', 'Nodes\Api\ServiceProvider'));
        }
    }

    /**
     * Register the class aliases.
     *
     * @return void
     */
    protected function registerClassAliases()
    {
        $aliases = [
            DingoHttpRequest::class => DingoContractHttpRequest::class,
            'api.dispatcher'        => DingoDispatcher::class,
            'api.http.validator'    => DingoHttpRequestValidator::class,
            'api.http.response'     => DingoHttpResponseFactory::class,
            'api.router'            => DingoRoutingRouter::class,
            'api.router.adapter'    => DingoContractRoutingAdapter::class,
            'api.auth'              => DingoAuth::class,
            'api.limiting'          => DingoRateLimitHandler::class,
            'api.transformer'       => DingoTransformerFactory::class,
            'api.url'               => DingoRoutingUrlGenerator::class,
            'api.exception'         => [
                DingoExceptionHandler::class,
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
            return new NodesExceptionHandler(
                $app['Illuminate\Contracts\Debug\ExceptionHandler'],
                config('nodes.api.errors.errorFormat'),
                config('nodes.api.errors.debug')
            );
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
            $dispatcher->setSubtype(config('nodes.api.settings.subtype'));
            $dispatcher->setStandardsTree(config('nodes.api.settings.standardsTree'));
            $dispatcher->setPrefix(config('nodes.api.settings.prefix'));
            $dispatcher->setDefaultVersion(config('nodes.api.settings.version'));
            $dispatcher->setDefaultDomain(config('nodes.api.settings.domain'));
            $dispatcher->setDefaultFormat(config('nodes.api.response.defaultFormat'));
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
            return new NodesAuth($app['api.router'], $app, prepare_config_instances(config('nodes.api.auth.providers')));
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
        $this->app->singleton(NodesConsoleCommandDocs::class, function ($app) {
            return new NodesConsoleCommandDocs(
                $app['api.router'],
                $app['Dingo\Blueprint\Blueprint'],
                $app['Dingo\Blueprint\Writer'],
                config('nodes.api.settings.name'),
                config('nodes.api.settings.version')
            );
        });

        $this->commands([NodesConsoleCommandDocs::class]);
    }
}