<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="/views/styles.css">
	<script src="https://hcaptcha.com/1/api.js" async defer></script>
	<script src="/views/scripts.js" defer></script>
</head>
<body>
<form id="loginForm" onsubmit="handleSubmit(event, 'loginForm', '/api/login')">
	<div>
		<h1>Login</h1>
	</div>
	<label for="username">Username or Email:</label>
	<input type="text" id="username" name="username" required><br>

	<label for="password">Password:</label>
	<input type="password" id="password" name="password" required><br>

	<div id="captcha-container" style="display:none;">
		<div class="h-captcha" data-sitekey="4a54ced3-d5d6-436a-939d-e110976a7018"></div>
	</div>

	<button type="submit">Login</button>

	<div id="message"></div>
	<a href="/register">Register</a><a href="/forgot-password">Forgot Password</a>
</form>
</body>
</html>
