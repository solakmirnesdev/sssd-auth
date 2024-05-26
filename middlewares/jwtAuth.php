<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('jwtAuth')) {
    /**
     * JWT Authentication middleware.
     *
     * This function checks for the presence of an Authorization header,
     * extracts the JWT token, and decodes it. If the token is valid,
     * it returns the decoded token; otherwise, it returns an error response.
     *
     * @return mixed Decoded JWT token if valid, false otherwise.
     */
    function jwtAuth() {
        // Retrieve the Authorization header from the request
        $authHeader = Flight::request()->headers->Authorization;

        // Check if the Authorization header is missing
        if (!$authHeader) {
            Flight::json(['error' => 'Authorization header missing'], 401);
            return false;
        }

        // Extract the JWT token from the Authorization header
        list($jwt) = sscanf($authHeader, 'Bearer %s');

        // Check if the token is missing
        if (!$jwt) {
            Flight::json(['error' => 'Token missing'], 401);
            return false;
        }

        try {
            // Decode the JWT token using the secret key and HS256 algorithm
            $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
            return $decoded; // Return the decoded token if valid
        } catch (Exception $e) {
            // Handle errors during token decoding (e.g., invalid token)
            Flight::json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
            return false;
        }
    }
}
