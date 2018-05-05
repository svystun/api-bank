<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
<h2>Welcome to the site {{ $user->name }}</h2>
<br/>Your registered email-id is: <strong>{{ $user->email }}</strong>.
<br/>Please click on the below link to verify your account: <a href="{{ route('activate', ['code' => $user->activation_code]) }}">Verify Email</a>
</body>
</html>