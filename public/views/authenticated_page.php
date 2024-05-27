<!DOCTYPE html>
<html>
<head>
	<title>2FA Verification</title>
	<link rel="stylesheet" type="text/css" href="/views/styles.css">
	<script src="/views/2fa-scripts.js" defer></script>
</head>
<body>
<h1>Two-Factor Authentication</h1>
<form id="2faForm" onsubmit="handle2FASubmit(event, '2faForm', '/api/verify2fa')">
	<label for="totp_code">Enter the 2FA code from your authenticator app:</label>
	<input type="text" id="totp_code" name="totp_code" required><br>
	<button type="submit">Verify</button><!DOCTYPE html>
	<html>
	<head>
		<title>Welcome</title>
	</head>
	<body>
	<h1>Authenticated!</h1>
	</body>
	</html>
	<div id="message"></div>
</form>
</body>
</html>
