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
    include '../src/Views/welcome_page.php';
});

// Route for authenticated page
Flight::route('GET /authenticated', function() {
    if (Flight::jwtAuth()) {
        include '../src/Views/authenticated.php';
    }
});
