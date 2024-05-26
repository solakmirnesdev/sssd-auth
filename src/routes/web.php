<?php

/**
 * Web Routes
 *
 * This file defines the routes for the web pages. These routes are used to
 * serve the HTML pages to the users. It includes the homepage and other
 * authenticated pages.
 */

// Root route serving the welcome page
Flight::route('GET /', function() {
    include '../src/views/welcome_page.php';
});

// Route for login page
Flight::route('GET /login', function() {
    include '../src/views/login_page.php';
});

// Route for register page
Flight::route('GET /register', function() {
    include '../src/views/register_page.php';
});

// Route for forgot password page
Flight::route('GET /forgot-password', function() {
    include '../src/views/forgotpassword_page.php';
});

// Route for authenticated page
Flight::route('GET /authenticated', function() {
    $decoded = Flight::jwtAuth();
    if ($decoded) {
        include '../src/views/authenticated_page.php';
    }
});
