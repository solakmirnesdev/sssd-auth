<?php

// SSSD project config default, has to be called config_default.php

// Database connection
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Supermar!o1');
define('DB_NAME', 'sssd_auth');
define('DB_PORT', '3306'); // Default MySQL port, change if different

// Text messages
define('TEXT_MESSAGE_API_KEY', 'your_text_message_api_key');
define('TEXT_MESSAGE_SECRET', ''); // Will be empty for Infobip

// Email
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_USERNAME', 'cb215d12ba5592');
define('SMTP_PASSWORD', '2d778308ff5ab9');
define('SMTP_PORT', '2525'); // Mailtrap port
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

// hcaptcha
define('HCAPTCHA_SERVER_SECRET', 'your_hcaptcha_server_secret');
define('HCAPTCHA_SITE_KEY', 'your_hcaptcha_site_key');

// JWT Secret
define('JWT_SECRET', 'somesecret');
