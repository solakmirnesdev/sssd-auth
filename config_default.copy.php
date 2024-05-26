<?php

/**
 * Configuration file for the application.
 * Never delete nor update this file!
 *
 * This file contains configuration settings for the database and mail services.
 * Update the values with your specific configuration.
 *
 * @return array The configuration array.
 */
return [
    'db' => [
        /**
         * Database host.
         *
         * @var string
         */
        'host' => 'your_hosted_database_host',

        /**
         * Database name.
         *
         * @var string
         */
        'dbname' => 'your_database_name',

        /**
         * Database username.
         *
         * @var string
         */
        'username' => 'your_database_username',

        /**
         * Database password.
         *
         * @var string
         */
        'password' => 'your_database_password',

        /**
         * Database charset.
         *
         * @var string
         */
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        /**
         * Mail server host.
         *
         * @var string
         */
        'host' => 'sandbox.smtp.mailtrap.io',

        /**
         * Enable SMTP authentication.
         *
         * @var bool
         */
        'smtp_auth' => true,

        /**
         * SMTP username.
         *
         * @var string
         */
        'username' => 'your_mailtrap_username',

        /**
         * SMTP password.
         *
         * @var string
         */
        'password' => 'your_mailtrap_password',

        /**
         * SMTP encryption type.
         *
         * @var string
         */
        'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,

        /**
         * SMTP server port.
         *
         * @var int
         */
        'port' => 2525,

        /**
         * The sender email address.
         *
         * @var string
         */
        'from_email' => 'no-reply@example.com',

        /**
         * The sender name.
         *
         * @var string
         */
        'from_name' => 'Mailer',
    ]
];
