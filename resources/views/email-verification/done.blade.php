@extends('nodes.api::email-verification.base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">Email verification</h3>
        </div>
        <div class="panel-body">
            @if (Session::has('success'))
                <div class="alert alert-success text-center" role="alert">
                    {{ Session::get('success') }}
                </div>
            @endif
            <p><strong>Congratulations!</strong></p>
            <p>Your email has been now been verified.</p>
            <p>
                <strong>Thank you for using our app</strong>,<br>
                {{ $senderName }}
            </p>
        </div>
    </div>
@stop