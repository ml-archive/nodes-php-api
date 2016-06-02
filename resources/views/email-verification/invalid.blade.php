@extends('nodes.api::email-verification.base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">Email verification</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-danger text-center" role="alert">
                Invalid token!
            </div>
            <p>The email verification token you're trying to use is invalid. Either this is because the token doesn't exist or because it has already been used.</p>
            <p>
                <strong>Thank you for using our app</strong>,<br>
                {{ $senderName }}
            </p>
        </div>
    </div>
@stop