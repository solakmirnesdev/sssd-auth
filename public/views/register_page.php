<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Register</title>
	<link rel="stylesheet" href="/views/styles.css">
	<script src="https://hcaptcha.com/1/api.js" async defer></script>
	<script src="/scripts.js"></script>
</head>
<body>
<div class="container">
	<h2>Register</h2>
	<form id="register-form" onsubmit="handleSubmit(event, 'register-form', '/api/register')">
		<label for="full_name">Full Name:</label>
		<input type="text" id="full_name" name="full_name" required>

		<label for="username">Username:</label>
		<input type="text" id="username" name="username" required>

		<label for="password">Password:</label>
		<input type="password" id="password" name="password" required>

		<label for="email">Email:</label>
		<input type="email" id="email" name="email" required>

		<label for="phone_number">Phone Number:</label>
		<input type="text" id="phone_number" name="phone_number" required>

		<div class="h-captcha" data-sitekey="<?php echo HCAPTCHA_SITE_KEY; ?>"></div>

		<button type="submit">Register</button>
	</form>
	<div id="message"></div>
	<div class="link-container">
		<a href="/">Back to Home</a> | <a href="/login">Login</a>
	</div>
</div>
</body>
</html>
