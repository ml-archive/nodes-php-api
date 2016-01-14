<?php
if (!function_exists('api_auth')) {
    /**
     * Retrieve authenticator instance
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Nodes\Api\Auth\Auth
     */
    function api_auth()
    {
        return \NodesAPI::auth();
    }
}

if (!function_exists('api_user')) {
    /**
     * Retrieve current authenticated user
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function api_user()
    {
        return \NodesAPI::user();
    }
}