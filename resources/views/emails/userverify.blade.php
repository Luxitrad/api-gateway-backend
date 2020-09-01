<!DOCTYPE html>
<html>
<head>
    <title>Confirmation Email</title>
</head>

<body>
  <h2>Welcome {{$user['firstname']}}</h2>
  <br/>
  Your registered email-id is {{$user['email']}} , Please click on the below link to verify your email account
  <br/>
  <a href="{{url('api/auth/user/verify', $user->verifyUser->token)}}">Verify Email</a>
</body>
</html>
