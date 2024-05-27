<?php

require '../vendor/autoload.php'; // Autoload composer dependencies
require '../config_default.php'; // Include the configuration file
require '../middlewares/jwtAuth.php'; // Include the middleware file

use Solakmirnes\SssdAuth\Database;

/**
 * Initialize the database connection.
 */
$database = Database::getInstance([
    'host' => DB_HOST,
    'dbname' => DB_NAME,
    'username' => DB_USERNAME,
    'password' => DB_PASSWORD,
    'charset' => 'utf8mb4',
]);

/**
 * Map the JWT authentication middleware.
 */
Flight::map('jwtAuth', 'jwtAuth');

/**
 * Include routes.
 */
require '../src/routes/web.php'; // Web routes
require '../src/routes/api.php'; // API routes

/**
 * Start the application.
 */
Flight::start();
