<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
<h1>Forgot Password</h1>
<form method="POST" action="/api/forgot-password">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <br>
    <button type="submit">Send Reset Link</button>
</form>
<a href="/login">Login</a>
<br>
<a href="/register">Register</a>
</body>
</html>
