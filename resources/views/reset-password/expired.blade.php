@extends('nodes.api::base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">Reset password</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-danger text-center" role="alert">
                Token has expired!
            </div>
            <p>Your reset password request has expired. To reset your password you need to request a new token.</p>
            <p>
                <strong>Thank you for using our app</strong>,<br>
                {{ $senderName }}
            </p>
        </div>
    </div>
@stop