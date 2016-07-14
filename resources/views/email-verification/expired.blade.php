@extends('nodes.api::base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">E-mail verification</h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-danger text-center" role="alert">
                Token has expired!
            </div>
            <p>Your e-mail verification has expired. Register again.</p>
            <p>
                <strong>Thank you for using our app</strong>,<br>
                {{ $senderName }}
            </p>
        </div>
    </div>
@stop