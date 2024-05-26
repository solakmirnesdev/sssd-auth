<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;

class UserController {
    public static function register() {
        $data = Flight::request()->data;

        // Validate full name
        if (empty($data->full_name)) {
            Flight::json(['error' => 'Full name is required'], 400);
            return;
        }

        // Validate username
        if (empty($data->username) || strlen($data->username) <= 3 || !ctype_alnum($data->username)) {
            Flight::json(['error' => 'Invalid username'], 400);
            return;
        }

        // Validate against reserved names
        $reservedNames = ['admin', 'root', 'system']; // add more reserved names as needed
        if (in_array(strtolower($data->username), $reservedNames)) {
            Flight::json(['error' => 'Username is reserved'], 400);
            return;
        }

        // Validate password
        if (empty($data->password) || strlen($data->password) < 8) {
            Flight::json(['error' => 'Password must be at least 8 characters long'], 400);
            return;
        }

        // Check password strength and uniqueness with Have I Been Pwned
        if (self::isPasswordPwned($data->password)) {
            Flight::json(['error' => 'Password has been compromised in a data breach'], 400);
            return;
        }

        // Validate email format
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::json(['error' => 'Invalid email address'], 400);
            return;
        }

        // Validate phone number format using Google's libphonenumber
        if (!self::isValidPhoneNumber($data->phone_number)) {
            Flight::json(['error' => 'Invalid phone number'], 400);
            return;
        }

        // Check if username or email already exists
        if (User::findByUsernameOrEmail($data->username, $data->email)) {
            Flight::json(['error' => 'Username or email already exists'], 400);
            return;
        }

        // Create user
        $userId = User::create($data->full_name, $data->username, $data->password, $data->email, $data->phone_number);

        // Send confirmation email
        self::sendConfirmationEmail($data->email, $userId);

        Flight::json(['message' => 'Registration successful! Please check your email to verify your account.']);
    }

    private static function isPasswordPwned($password) {
        // Implement Have I Been Pwned API check here
        return false; // Return true if password is pwned, otherwise false
    }

    private static function isValidPhoneNumber($phoneNumber) {
        // Implement Google's libphonenumber check here
        return true; // Return true if phone number is valid, otherwise false
    }

    private static function sendConfirmationEmail($email, $userId) {
        // Implement email sending functionality here
    }
}
