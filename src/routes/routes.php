<?php
Flight::route('GET /', function(){
    echo 'Welcome to my Secure Software System Development App!';
});

Flight::route('POST /register', ['Solakmirnes\\SssdAuth\\Controllers\\UserController', 'register']);
Flight::route('POST /login', ['Solakmirnes\\SssdAuth\\Controllers\\UserController', 'login']);
