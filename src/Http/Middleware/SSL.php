<?php

namespace Nodes\Api\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SSL.
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 */
class SSL
{
    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param         $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && env('APP_ENV') == 'production') {
            throw new BadRequestHttpException('Only secure requests', null, 400);
        }

        return $next($request);
    }
}
