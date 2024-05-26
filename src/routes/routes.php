<?php

require '../vendor/autoload.php'; // Autoload composer dependencies

// Include JWT Auth middleware only once
require_once '../middlewares/jwtAuth.php'; // Include the middleware file

/**
 * Map the JWT authentication middleware.
 *
 * This maps the 'jwtAuth' middleware function to Flight,
 * making it available for route protection.
 */
Flight::map('jwtAuth', 'jwtAuth');

/**
 * Root route.
 *
 * This route displays a welcome message when the root URL is accessed.
 *
 * @route GET /
 */
Flight::route('GET /', function(){
    echo 'Welcome to my App!';
});

/**
 * User registration route.
 *
 * This route handles user registration. It calls the `register` method in the `UserController`.
 *
 * @route POST /register
 */
Flight::route('POST /register', ['Solakmirnes\SssdAuth\Controllers\UserController', 'register']);

/**
 * User login route.
 *
 * This route handles user authentication. It calls the `login` method in the `UserController`.
 *
 * @route POST /login
 */
Flight::route('POST /login', ['Solakmirnes\SssdAuth\Controllers\UserController', 'login']);

/**
 * Email verification route.
 *
 * This route handles email verification. It calls the `verifyEmail` method in the `UserController`.
 *
 * @route GET /verify
 */
Flight::route('GET /verify', ['Solakmirnes\SssdAuth\Controllers\UserController', 'verifyEmail']);

/**
 * Forgot password route.
 *
 * This route handles password reset requests. It calls the `forgotPassword` method in the `UserController`.
 *
 * @route POST /forgot-password
 */
Flight::route('POST /forgot-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'forgotPassword']);

/**
 * Password reset form route.
 *
 * This route displays the password reset form. It calls the `showResetForm` method in the `UserController`.
 *
 * @route GET /reset-password
 */
Flight::route('GET /reset-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'showResetForm']);

/**
 * Password reset route.
 *
 * This route handles password reset submissions. It calls the `resetPassword` method in the `UserController`.
 *
 * @route POST /reset-password
 */
Flight::route('POST /reset-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'resetPassword']);

/**
 * Example of a protected route.
 *
 * This route is an example of a protected route that requires JWT authentication.
 * It calls the `jwtAuth` middleware to verify the token before allowing access.
 *
 * @route GET /protected
 */
Flight::route('GET /protected', function() {
    if (Flight::jwtAuth()) {
        Flight::json(['message' => 'You have accessed a protected route']);
    }
});

/**
 * Start the Flight PHP application.
 *
 * This starts the Flight PHP application, making it ready to handle requests.
 */
Flight::start();
