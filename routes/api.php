<?php
api_version('v1', ['namespace' => 'Nodes\Api\Auth\ResetPassword', 'prefix' => 'api/reset-password'], function() {
    // Generate reset password token
    api_post('/token', 'ResetPasswordController@generateResetToken');
});