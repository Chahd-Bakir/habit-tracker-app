<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing in...</title>
</head>
<body>
<script>
    localStorage.setItem('auth_token', @json($token));
    localStorage.setItem('auth_user', @json($user));
    window.location.replace(@json($redirectUrl));
</script>
</body>
</html>