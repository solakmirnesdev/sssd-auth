<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h1>Login</h1>
<form method="POST" action="/api/login">
    <label for="username">Username or Email:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Login</button>
</form>
<a href="/forgot-password">Forgot Password?</a>
<br>
<a href="/register">Register</a>
</body>
</html>