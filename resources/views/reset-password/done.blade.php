@extends('nodes.api::reset-password.base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">Reset password</h3>
        </div>
        <div class="panel-body">
            @if (Session::has('success'))
                <div class="alert alert-success text-center" role="alert">
                    {{ Session::get('success') }}
                </div>
            @endif
            <p><strong>Congratulations!</strong></p>
            <p>Your password has been now been updated and you can now delete the before sent e-mail.</p>
            <p>
                <strong>Thank you for using our app</strong>,<br>
                {{ $senderName }}
            </p>
        </div>
    </div>
@stop