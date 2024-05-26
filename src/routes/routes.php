<?php

/**
 * Root route.
 *
 * This route displays a welcome message when the root URL is accessed.
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
 * Password reset request route.
 *
 * This route handles password reset requests. It calls the `forgotPassword` method in the `UserController`.
 *
 * @route POST /forgot-password
 */
Flight::route('POST /forgot-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'forgotPassword']);

/**
 * Password reset route.
 *
 * This route handles password resets. It calls the `resetPassword` method in the `UserController`.
 *
 * @route POST /reset-password
 */
Flight::route('POST /reset-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'resetPassword']);

/**
 * Password reset form route.
 *
 * This route displays the password reset form. It calls the `showResetForm` method in the `UserController`.
 *
 * @route GET /reset-password
 */
Flight::route('GET /reset-password', ['Solakmirnes\SssdAuth\Controllers\UserController', 'showResetForm']);
