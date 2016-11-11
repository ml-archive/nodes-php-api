<?php

namespace Nodes\Api\Http\Middleware;

use Closure;
use Nodes\Api\Exceptions\InvalidUserAgent;
use Nodes\Support\UserAgent\Parser;

/**
 * Class Meta
 *
 * @package Nodes\Api\Http\Middleware
 */
class Meta
{
    /**
     * handle
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @access public
     *
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \Nodes\Api\Exceptions\InvalidUserAgent
     */
    public function handle($request, Closure $next)
    {
        // See if env require N-meta header
        if (in_array(env('APP_ENV'), NodesMeta::getMetaEnvironments())) {
            // Only accept requests with nodes meta
            if (config('nodes.api.settings.strictNodesMetaHeader', false) && ! nodes_meta()) {
                throw (new InvalidUserAgent(sprintf('Missing [%s] header', Parser::NODES_META_HEADER), 400))->setStatusCode(400);
            }
        }

        return $next($request);
    }
}
