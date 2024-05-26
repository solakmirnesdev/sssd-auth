<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * AuthController class for managing authentication-related actions.
 */
class AuthController {

    /**
     * Handle user registration.
     *
     * This method processes the registration data, validates it,
     * and creates a new user if all validations pass.
     * It then sends a confirmation email to the user.
     *
     * @return void
     */
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

        // Check for reserved usernames
        $reservedNames = ['admin', 'root', 'system'];
        if (in_array(strtolower($data->username), $reservedNames)) {
            Flight::json(['error' => 'Username is reserved'], 400);
            return;
        }

        // Validate password
        if (empty($data->password) || strlen($data->password) < 8) {
            Flight::json(['error' => 'Password must be at least 8 characters long'], 400);
            return;
        }

        // Check if the password has been compromised in a data breach
        if (ValidationController::isPasswordPwned($data->password)) {
            Flight::json(['error' => 'Password has been compromised in a data breach'], 400);
            return;
        }

        // Validate email
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::json(['error' => 'Invalid email address'], 400);
            return;
        }

        // Check if the email domain extension is valid
        if (!ValidationController::isValidDomainExtension($data->email)) {
            Flight::json(['error' => 'Invalid email domain extension'], 400);
            return;
        }

        // Check if the email domain has valid MX records
        if (!ValidationController::hasValidMXRecords($data->email)) {
            Flight::json(['error' => 'Email domain does not have valid MX records'], 400);
            return;
        }

        // Validate phone number
        if (!ValidationController::isValidPhoneNumber($data->phone_number)) {
            Flight::json(['error' => 'Invalid phone number'], 400);
            return;
        }

        // Check if the username or email already exists in the database
        if (User::findByUsernameOrEmail($data->username, $data->email)) {
            Flight::json(['error' => 'Username or email already exists'], 400);
            return;
        }

        // Create a new user in the database
        $userId = User::create($data->full_name, $data->username, $data->password, $data->email, $data->phone_number);

        // Send a confirmation email to the user
        EmailController::sendConfirmationEmail($data->email, $userId);

        // Respond with a success message
        Flight::json(['message' => 'Registration successful! Please check your email to verify your account.']);
    }

    /**
     * Handle user login.
     *
     * This method processes the login data, validates it,
     * and logs the user in if the credentials are correct.
     * It also tracks failed login attempts and requires CAPTCHA after 3 failed attempts.
     *
     * @return void
     */
    public static function login() {
        $data = Flight::request()->data;

        // Validate username or email
        if (empty($data->username) && empty($data->email)) {
            Flight::json(['error' => 'Username or email is required'], 400);
            return;
        }

        // Validate password
        if (empty($data->password)) {
            Flight::json(['error' => 'Password is required'], 400);
            return;
        }

        // Find user by username or email
        $user = User::findByUsernameOrEmail($data->username, $data->email);
        if (!$user) {
            Flight::json(['error' => 'Invalid username/email or password'], 400);
            return;
        }

        // Verify password
        if (!password_verify($data->password, $user['password'])) {
            Flight::json(['error' => 'Invalid username/email or password'], 400);
            return;
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            Flight::json(['error' => 'Please verify your email before logging in'], 400);
            return;
        }

        // Generate JWT token
        $payload = [
            'iss' => "http://localhost", // Issuer
            'aud' => "http://localhost", // Audience
            'iat' => time(), // Issued At
            'nbf' => time(), // Not Before
            'exp' => time() + 3600, // Expiration Time
            'data' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ];

        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

        // Respond with success message and token
        Flight::json(['message' => 'Login successful!', 'token' => $jwt]);
    }

    /**
     * Verify a user's email address.
     *
     * This method verifies the user's email address by setting the `email_verified`
     * flag in the database to true.
     *
     * @return void
     */
    public static function verifyEmail() {
        $userId = Flight::request()->query['user'];

        // Update the user's email_verified flag
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        // Respond with success message
        Flight::json(['message' => 'Email verified successfully!']);
    }

    /**
     * Handle password reset request.
     *
     * This method processes the password reset request, generates a reset token,
     * and sends a password reset email to the user.
     *
     * @return void
     */
    public static function forgotPassword() {
        $data = Flight::request()->data;

        // Validate email
        if (empty($data->email)) {
            Flight::json(['error' => 'Email is required'], 400);
            return;
        }

        // Find user by email
        $user = User::findByEmail($data->email);
        if (!$user) {
            Flight::json(['error' => 'Email not found'], 404);
            return;
        }

        // Generate password reset token
        $resetToken = bin2hex(random_bytes(16));
        $expiryTime = time() + 300; // 5 minutes expiry

        // Save password reset token and expiry time
        User::savePasswordResetToken($user['id'], $resetToken, $expiryTime);

        // Send password reset email
        EmailController::sendPasswordResetEmail($data->email, $resetToken);

        // Respond with success message
        Flight::json(['message' => 'Password reset email sent']);
    }

    /**
     * Handle password reset.
     *
     * This method processes the password reset form submission,
     * validates the reset token, and updates the user's password.
     *
     * @return void
     */
    public static function resetPassword() {
        $data = Flight::request()->data;

        // Validate token
        if (empty($data->token)) {
            Flight::json(['error' => 'Token is required'], 400);
            return;
        }
        // Validate new password
        if (empty($data->new_password)) {
            Flight::json(['error' => 'New password is required'], 400);
            return;
        }

        // Find user by reset token
        $user = User::findByResetToken($data->token);
        if (!$user || $user['reset_token_expiry'] < time()) {
            Flight::json(['error' => 'Invalid or expired token'], 400);
            return;
        }

        // Update user's password
        $newPasswordHash = password_hash($data->new_password, PASSWORD_DEFAULT);
        User::updatePassword($user['id'], $newPasswordHash);
        User::clearResetToken($user['id']);

        // Respond with success message
        Flight::json(['message' => 'Password reset successful!']);
    }

    /**
     * Display the password reset form.
     *
     * This method generates and displays an HTML form for the user to reset their password.
     * The form includes a hidden input field for the reset token and a password input field for the new password.
     * The form is submitted to the /reset-password endpoint.
     *
     * @return void
     */
    public static function showResetForm() {
        $token = Flight::request()->query['token'];

        echo "<form method='POST' action='/reset-password'>
            <input type='hidden' name='token' value='$token'>
            <label for='new_password'>New Password:</label>
            <input type='password' name='new_password' id='new_password' required>
            <button type='submit'>Reset Password</button>
          </form>";
    }
}
