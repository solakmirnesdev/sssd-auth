<?php

require '../vendor/autoload.php'; // Autoload composer dependencies
require '../config_default.php'; // Include the configuration file
require '../middlewares/jwtAuth.php'; // Include the middleware file

use Solakmirnes\SssdAuth\Database;

/**
 * Initialize the database connection.
 *
 * This initializes the singleton instance of the Database class using
 * the configuration values defined in config_default.php.
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
 *
 * This maps the 'jwtAuth' middleware function to Flight,
 * making it available for route protection.
 */
Flight::map('jwtAuth', 'jwtAuth');

/**
 * Include route files.
 *
 * The web.php file defines the routes for the web pages,
 * and the api.php file defines the routes for the API endpoints.
 */
require '../src/routes/web.php';
require '../src/routes/api.php';

/**
 * Start the application.
 *
 * This starts the Flight PHP application, making it ready to handle requests.
 */
Flight::start();
