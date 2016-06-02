<?php
Route::group(['namespace' => 'Nodes\Api\Auth\ResetPassword', 'prefix' => 'reset-password'], function() {
    // Reset password form
    Route::get('/{token}', [
        'as' => 'nodes.api.auth.reset-password.form',
        'uses' => 'ResetPasswordController@index',
    ])->where('token', '[[:alnum:]]{64}');

    // Change password
    Route::post('/', [
        'as' => 'nodes.api.auth.reset-password.reset',
        'uses' => 'ResetPasswordController@resetPassword',
    ]);

    // Reset password done
    Route::get('/done', [
        'as' => 'nodes.api.auth.reset-password.done',
        'uses' => 'ResetPasswordController@done',
    ]);
});

Route::group(['namespace' => 'Nodes\Api\Auth\EmailVerification', 'prefix' => 'email-verification'], function() {

    // Confirm email
    Route::get('/{token}/{email}', [
        'as' => 'nodes.api.auth.email-verificatio.confirm',
        'uses' => 'EmailVerificationController@index',
    ])->where('token', '[[:alnum:]]{64}');
});
