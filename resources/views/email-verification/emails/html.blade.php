<html>
<head>
    <title>Email verification</title>
</head>
</html>

<body>
    <h4>Hello,</h4>
    <p>
        We had request to register account with this email.
    </p>
    <p>
        To verify your account, click the following link:<br>
        <a href="{{ $domain }}/email-verification/{{ $token }}/{{$email}}">{{ $domain }}/email-verification/{{ $token }}/{{$email}}</a>
    </p>
    <p>
        <em>This email verification request will expire in {{ $expire }} minutes.</em>
    </p>
    <h4>
        Thank you for using our app,<br>
        {{ $senderName }}
    </h4>
</body>
</html>