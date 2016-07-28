<?php

namespace Nodes\Api\Http\Middleware;

use Closure;
use Nodes\Api\Exceptions\InvalidUserAgent;

/**
 * Class UserAgent.
 */
class UserAgent
{
    /**
     * Handle an incoming request.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     * @throws \Nodes\Api\Exceptions\InvalidUserAgent
     */
    public function handle($request, Closure $next)
    {
        // Only accept requests from Nodes user agents
        $nodesUserAgent = nodes_user_agent();
        if (empty($nodesUserAgent)) {
            throw (new InvalidUserAgent('Invalid user agent. Reason: Could not locate Nodes details.', 400))->setStatusCode(400);
        }

        return $next($request);
    }
}
