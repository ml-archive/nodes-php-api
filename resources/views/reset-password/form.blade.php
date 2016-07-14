@extends('nodes.api::base')

@section('content')
    <div id="nBox" class="panel panel-default nodes-center">
        <div class="panel-heading">
            <h3 class="panel-title">Reset password</h3>
        </div>
        <div class="panel-body">
            @if (Session::has('error'))
                <div class="alert alert-danger text-center" role="alert">
                    {{ Session::get('error') }}
                </div>
            @endif
            <p>Enter the e-mail address of the user who's password you wish to reset. Here after enter the user's new password.</p>
            {!! Form::open(['method' => 'post', 'route' => 'nodes.api.auth.reset-password.reset']) !!}
            {!! Form::hidden('token', $token) !!}
            <div class="form-group">
                {!! Form::label('nResetPasswordEmail', 'E-mail address') !!}
                {!! Form::email('email', Session::get('email'), ['id' => 'nResetPasswordEmail', 'class' => 'form-control', 'placeholder' => 'your@email.com']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('nResetPasswordNew', 'New password') !!}
                {!! Form::password('password', ['id' => 'nResetPasswordNew', 'class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('nResetPasswordRepeat', 'Repeat password') !!}
                {!! Form::password('repeat-password', ['id' => 'nResetPasswordRepeat', 'class' => 'form-control']) !!}
            </div>
            <div class="form-group">
                {!! Form::submit('Change password', ['class' => 'btn btn-primary form-control']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@stop