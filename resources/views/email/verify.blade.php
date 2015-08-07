<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Verify Your Email Address</h2>

<div>
    Please follow the link below to verify your email address:
    <a href="{{ action('SummonerController@verify', [$confirmation_code]) }}" target="_blank">verify email</a>.<br>
    This email expires in 1 hour.
</div>
</body>
</html>