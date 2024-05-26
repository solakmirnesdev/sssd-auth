<?php

Flight::route('GET /', function(){
    echo 'Welcome to my App!';
});

// Create user
Flight::route('POST /register', ['Solakmirnes\SssdAuth\Controllers\UserController', 'register']);

// Verification route
Flight::route('GET /verify', ['Solakmirnes\SssdAuth\Controllers\UserController', 'verifyEmail']);
