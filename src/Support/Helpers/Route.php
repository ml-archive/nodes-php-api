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
            return app('api.router')->version($version, $options);
        } else {
            return app('api.router')->version($version, $options, $callback);
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
        return app('api.router')->group($attributes, $callback);
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
     * @return \Illuminate\Routing\Route
     */
    function api_get($uri, $action)
    {
        return app('api.router')->get($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_post($uri, $action)
    {
        return app('api.router')->post($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_put($uri, $action)
    {
        return app('api.router')->put($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_patch($uri, $action)
    {
        return app('api.router')->patch($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_delete($uri, $action)
    {
        return app('api.router')->delete($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_options($uri, $action)
    {
        return app('api.router')->options($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_any($uri, $action)
    {
        return app('api.router')->any($uri, $action);
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
     * @return \Illuminate\Routing\Route
     */
    function api_match($methods, $uri, $action)
    {
        return app('api.router')->match($methods, $uri, $action);
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
        return app('api.router')->resources($resources);
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
        return app('api.router')->resource($name, $controller, $options);
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
        return app('api.router')->controllers($controllers);
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
        return app('api.router')->controller($uri, $controller, $names);
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
        return app('api.router')->current();
    }
}