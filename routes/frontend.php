<?php
Route::group(['namespace' => 'Nodes\Api\Auth\ResetPassword', 'prefix' => 'reset-password'], function() {
    // Reset password form
    Route::get('/{token}', [
        'as' => 'nodes.api.auth.reset-password.form',
        'uses' => 'ResetPasswordController@index',
    ])->where('token', '[[:alnum:]]{64}');

    // Change password
    Route::post('/', [
        'as' => 'nodes.api.auth.reset-password.reset.update',
        'uses' => 'ResetPasswordController@resetPassword',
    ]);

    // Reset password done
    Route::get('/done', [
        'as' => 'nodes.api.auth.reset-password.done',
        'uses' => 'ResetPasswordController@done',
    ]);
});