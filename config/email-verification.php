<?php
/*
|--------------------------------------------------------------------------
| Email verification
|--------------------------------------------------------------------------
|
| Settings used by the email verification feature.
|
| Change the templates used to generate the e-mails or/and sender name
| and subject of the e-mail.
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable e-mail verification
    |--------------------------------------------------------------------------
    */
    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Table with email verification tokens
    |--------------------------------------------------------------------------
    */
    'table' => 'user_verifications',

    /*
    |--------------------------------------------------------------------------
    | E-mail sender details
    |--------------------------------------------------------------------------
    */
    'from' => [
        'name' => 'Nodes',
        'email' => 'no-reply@nodes.dk'
    ],

    /*
    |--------------------------------------------------------------------------
    | Subject of e-mail
    |--------------------------------------------------------------------------
    */
    'subject' => 'Please verify your account',

    /*
    |--------------------------------------------------------------------------
    | E-mail templates
    |--------------------------------------------------------------------------
    |
    | Set the view path to e-mail templates, that will be used
    | to generate the reset password e-mails
    |
    */
    'views' => [
        'html' => 'nodes.api::email-verification.emails.html',
        'text' => 'nodes.api::email-verification.emails.text'
    ],

    /*
    |--------------------------------------------------------------------------
    | Token lifetime
    |--------------------------------------------------------------------------
    |
    | Set the lifetime of each generated token in minutes.
    | Default: 60 minutes
    |
    */
    'expire' => 60

];