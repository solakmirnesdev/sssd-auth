<!DOCTYPE html>
<html>
<head>
	<title>Forgot Password</title>
	<link rel="stylesheet" type="text/css" href="/views/styles.css">
	<script src="/views/scripts.js"></script>
</head>
<body>
<h1>Forgot Password</h1>
<form id="forgotPasswordForm" onsubmit="handleSubmit(event, 'forgotPasswordForm', '/api/forgot-password')">
	<label for="email">Email:</label>
	<input type="email" id="email" name="email" required><br>

	<button type="submit">Reset Password</button>
</form>
<div id="message"></div>
<a href="/login">Login</a> | <a href="/register">Register</a>
</body>
</html>
