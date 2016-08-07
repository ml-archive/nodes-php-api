<?php

namespace Nodes\Api\Routing;

use Dingo\Api\Routing\Helpers as DingoRoutingHelpers;

/**
 * Class Helpers.
 *
 * @trait
 *
 * @property \Illuminate\Database\Eloquent\Model $user
 * @property \Nodes\Api\Auth\Auth                $auth
 * @property \Nodes\Api\Http\Response\Factory    $response
 * @method Response item() item($item, $transformer, array $parameters = [], Closure $after = null) Bind an item to a transformer and start building a response
 */
trait Helpers
{
    use DingoRoutingHelpers;

    /**
     * Get the authenticated user.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function user()
    {
        return app('api.auth')->user();
    }

    /**
     * Get the auth instance.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Nodes\Api\Auth\Auth
     */
    protected function auth()
    {
        return app('api.auth');
    }

    /**
     * Get the response factory instance.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Nodes\Api\Http\Response\Factory
     */
    protected function response()
    {
        return app('api.http.response');
    }
}
