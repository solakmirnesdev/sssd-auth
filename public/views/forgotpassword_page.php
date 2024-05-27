<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Forgot Password</title>
	<link rel="stylesheet" href="/views/styles.css">
	<script src="https://hcaptcha.com/1/api.js" async defer></script>
	<script src="/scripts.js"></script>
</head>
<body>
<div class="container">
	<h2>Forgot Password</h2>
	<form id="forgot-password-form" onsubmit="handleSubmit(event, 'forgot-password-form', '/api/forgot-password')">
		<label for="email">Email:</label>
		<input type="email" id="email" name="email" required>

		<div class="h-captcha" data-sitekey="<?php echo HCAPTCHA_SITE_KEY; ?>"></div>

		<button type="submit">Send Reset Link</button>

		<div id="message"></div>
		<div class="link-container">
			<a href="/login">Login</a><a href="/register">Register</a>
		</div>
	</form>
</div>
</body>
</html>
