<?php
namespace Nodes\Api\Http\Middleware;

use Dingo\Api\Http\Middleware\Auth as DingoHttpMiddlewareAuth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;;
use Nodes\Api\Auth\Auth as Authentication;
use Nodes\Api\Auth\Exceptions\UnVerifiedException;
use Nodes\Api\Routing\Router;
use Closure;

/**
 * Class Auth
 *
 * @package Nodes\Api\Http\Middleware
 */
class Auth extends DingoHttpMiddlewareAuth
{
    /**
     * Auth constructor.
     * @param Router $router
     * @param Authentication $auth
     */
    public function __construct(Router $router, Authentication $auth)
    {
        $this->router = $router;
        $this->auth = $auth;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     * @throws UnVerifiedException
     * @author Paulius Navickas <pana@nodes.dk>
     */
    public function handle($request, Closure $next)
    {
        $route = $this->router->getCurrentRoute();

        if (! $this->auth->check(false)) {
            $this->auth->authenticate($route->getAuthenticationProviders());
        }

        if(config('nodes.api.email-verification.active') && !api_user()->verified_at){
            throw new UnVerifiedException();
        }

        return $next($request);
    }
    
}