<?php
namespace Nodes\Api\Support\Traits;

use Dingo\Api\Http\Parser\Accept as DingoHttpAcceptParser;
use Dingo\Api\Http\RateLimit\Handler as DingoRateLimitHandler;
use Dingo\Api\Http\RequestValidator as DingoHttpRequestValidator;
use Dingo\Api\Http\Validation\Accept as DingoHttpValidatorAccept;
use Dingo\Api\Http\Validation\Domain as DingoHttpValidatorDomain;
use Dingo\Api\Http\Validation\Prefix as DingoHttpValidatorPrefix;
use Nodes\Api\Http\Middleware\Auth as NodesHttpMiddlewareAuth;
use Nodes\Api\Http\Middleware\PrepareController as NodesHttpMiddlewarePrepareController;
use Nodes\Api\Http\Middleware\Ratelimit as NodesHttpMiddlewareRateLimit;
use Nodes\Api\Http\Middleware\Request as NodesHttpMiddlewareRequest;
use Nodes\Api\Http\Response as NodesHttpResponse;
use Nodes\Api\Http\Response\Factory as NodesHttpResponseFactory;

trait DingoHttpServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerHttpServiceProvider()
    {
        $this->registerRateLimiting();
        $this->registerHttpValidation();
        $this->registerHttpParsers();
        $this->registerResponseFactory();
        $this->registerMiddleware();
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
            return new DingoRateLimitHandler($app, $app['cache'], prepare_config_instances(config('nodes.api.throttling')));
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
            return new DingoHttpValidatorDomain(config('nodes.api.settings.domain'));
        });

        $this->app->singleton('Dingo\Api\Http\Validation\Prefix', function ($app) {
            return new DingoHttpValidatorPrefix(config('nodes.api.settings.prefix'));
        });

        $this->app->singleton('Dingo\Api\Http\Validation\Accept', function ($app) {
            return new DingoHttpValidatorAccept(
                $this->app['Dingo\Api\Http\Parser\Accept'],
                config('nodes.api.settings.strict')
            );
        });
    }

    /**
     * Register the HTTP parsers.
     *
     * @return void
     */
    protected function registerHttpParsers()
    {
        $this->app->singleton('Dingo\Api\Http\Parser\Accept', function ($app) {
            return new DingoHttpAcceptParser(
                config('nodes.api.settings.standardsTree'),
                config('nodes.api.settings.subtype'),
                config('nodes.api.settings.version'),
                config('nodes.api.response.defaultFormat')
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
        $this->app->singleton(NodesHttpMiddlewareRequest::class, function ($app) {
            $middleware = new NodesHttpMiddlewareRequest(
                $app,
                $app['api.exception'],
                $app['api.router'],
                $app['api.http.validator'],
                $app['events']
            );
            $middleware->setMiddlewares(config('nodes.api.middleware', []));
            return $middleware;
        });

        $this->app->singleton(NodesHttpMiddlewareAuth::class, function ($app) {
            return new NodesHttpMiddlewareAuth($app['api.router'], $app['api.auth']);
        });

        $this->app->singleton(NodesHttpMiddlewareRateLimit::class, function ($app) {
            return new NodesHttpMiddlewareRateLimit($app['api.router'], $app['api.limiting']);
        });

        $this->app->singleton(NodesHttpMiddlewarePrepareController::class, function ($app) {
            return new NodesHttpMiddlewarePrepareController($app['api.router']);
        });
    }
}