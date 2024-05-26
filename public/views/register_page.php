<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<link rel="stylesheet" type="text/css" href="/views/styles.css">
	<script src="/views/scripts.js"></script>
</head>
<body>
<form id="registerForm" onsubmit="handleSubmit(event, 'registerForm', '/api/register')">
	<h1>Register</h1>
	<label for="full_name">Full Name:</label>
	<input type="text" id="full_name" name="full_name" required><br>

	<label for="username">Username:</label>
	<input type="text" id="username" name="username" required><br>

	<label for="password">Password:</label>
	<input type="password" id="password" name="password" required><br>

	<label for="email">Email:</label>
	<input type="email" id="email" name="email" required><br>

	<label for="phone_number">Phone Number:</label>
	<input type="text" id="phone_number" name="phone_number" required><br>

	<button type="submit">Register</button>

	<div id="message"></div>
	<a href="/login">Login</a>
</form>
</body>
</html>
