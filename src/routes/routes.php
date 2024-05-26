<?php

Flight::route('GET /', function(){
    echo 'Welcome to the Secure Software System!';
});

Flight::route('POST /register', ['Solakmirnes\SssdAuth\Controllers\UserController', 'register']);
