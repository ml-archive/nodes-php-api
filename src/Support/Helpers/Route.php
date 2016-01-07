<?php
if (!function_exists('api_version')) {
    /**
     * An alias for calling the group method, allows a more fluent API
     * for registering a new API version group with optional
     * attributes and a required callback
     *
     * This method can be called without the third parameter, however,
     * the callback should always be the last paramter
     *
     * @author Morten Rugaard <moru@nodes.dk>
     * @param  string         $version
     * @param  array|callable $options
     * @param  callable       $callback
     * @return void
     */
    function api_version($version, $options, $callback = null)
    {
        if (func_num_args() == 2) {
            return \APIRoute::version($version, $options);
        } else {
            return \APIRoute::version($version, $options, $callback);
        }
    }
}

if (!function_exists('api_group')) {
    /**
     * Create a new route group
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array    $attributes
     * @param  callable $callback
     * @return void
     */
    function api_group(array $attributes, $callback)
    {
        return \APIRoute::group($attributes, $callback);
    }
}

if (!function_exists('api_get')) {
    /**
     * Create a new GET route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_get($uri, $action)
    {
        return \APIRoute::get($uri, $action);
    }
}

if (!function_exists('api_post')) {
    /**
     * Create a new POST route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_post($uri, $action)
    {
        return \APIRoute::post($uri, $action);
    }
}

if (!function_exists('api_put')) {
    /**
     * Create a new PUT route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_put($uri, $action)
    {
        return \APIRoute::put($uri, $action);
    }
}

if (!function_exists('api_patch')) {
    /**
     * Create a new PATCH route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_patch($uri, $action)
    {
        return \APIRoute::patch($uri, $action);
    }
}

if (!function_exists('api_delete')) {
    /**
     * Create a new DELETE route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_delete($uri, $action)
    {
        return \APIRoute::delete($uri, $action);
    }
}

if (!function_exists('api_options')) {
    /**
     * Create a new OPTIONS route
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_options($uri, $action)
    {
        return \APIRoute::options($uri, $action);
    }
}

if (!function_exists('api_any')) {
    /**
     * Create a new route that responding to all verbs
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_any($uri, $action)
    {
        return \APIRoute::any($uri, $action);
    }
}

if (!function_exists('api_match')) {
    /**
     * Create a new route with the given verbs
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array|string          $methods
     * @param  string                $uri
     * @param  array|string|callable $action
     * @return void
     */
    function api_match($methods, $uri, $action)
    {
        return \APIRoute::match($methods, $uri, $action);
    }
}

if (!function_exists('api_resources')) {
    /**
     * Register an array of resources
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array $resources
     * @return void
     */
    function api_resources(array $resources)
    {
        return \APIRoute::resources($resources);
    }
}

if (!function_exists('api_resource')) {
    /**
     * Register a resource controller
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $name
     * @param  string $controller
     * @param  array  $options
     * @return void
     */
    function api_resource($name, $controller, array $options = [])
    {
        return \APIRoute::resource($name, $controller, $options);
    }
}

if (!function_exists('api_controllers')) {
    /**
     * Register an array of controllers
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  array $controllers
     * @return void
     */
    function api_controllers(array $controllers)
    {
        return \APIRoute::controllers($controllers);
    }
}

if (!function_exists('api_controller')) {
    /**
     * Register a controller
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param  string $uri
     * @param  string $controller
     * @param  array  $names
     * @return void
     */
    function api_controller($uri, $controller, $names = [])
    {
        return \APIRoute::controller($uri, $controller, $names);
    }
}

if (!function_exists('api_current_route')) {
    /**
     * Get the currently dispatched route instance.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Illuminate\Routing\Route
     */
    function api_current_route()
    {
        return \APIRoute::current();
    }
}