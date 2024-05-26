<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use Solakmirnes\SssdAuth\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController {

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

        // Validate against reserved names
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

        // Check if password has been pwned
        if (self::isPasswordPwned($data->password)) {
            Flight::json(['error' => 'Password has been compromised in a data breach'], 400);
            return;
        }

        // Validate email format
        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::json(['error' => 'Invalid email address'], 400);
            return;
        }

        // Validate phone number format
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

    /**
     * Check if a password has been pwned using the Have I Been Pwned API.
     *
     * This method sends the first 5 characters of the SHA-1 hash of the password
     * to the Have I Been Pwned API and checks if the password has been found in a data breach.
     *
     * @param string $password The password to check.
     * @return bool True if the password has been pwned, false otherwise.
     */
    private static function isPasswordPwned($password) {
        return false; // Placeholder implementation
    }

    /**
     * Validate a phone number using the Google libphonenumber library.
     *
     * This method validates the format of the phone number to ensure it is a valid
     * mobile number.
     *
     * @param string $phoneNumber The phone number to validate.
     * @return bool True if the phone number is valid, false otherwise.
     */
    private static function isValidPhoneNumber($phoneNumber) {
        return true; // Placeholder implementation
    }

    /**
     * Send a confirmation email to the user.
     *
     * This method sends an email containing a verification link to the user's email address.
     *
     * @param string $email The user's email address.
     * @param int $userId The user's ID.
     * @return void
     */
    private static function sendConfirmationEmail($email, $userId) {
        $mail = new PHPMailer(true);
        try {
            // Server settings for Mailtrap
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'cb215d12ba5592';
            $mail->Password = '2d778308ff5ab9';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;

            // Recipients
            $mail->setFrom('no-reply@example.com', 'Mailer');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body    = "Please click on the following link to verify your email: <a href='http://localhost:8000/verify?user=$userId'>Verify Email</a>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
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

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        Flight::json(['message' => 'Email verified successfully!']);
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
        session_start(); // Start the session to track login attempts

        $data = Flight::request()->data;

        // Validating input
        if (empty($data->username) && empty($data->email)) {
            Flight::json(['error' => 'Username or email is required'], 400);
            return;
        }
        if (empty($data->password)) {
            Flight::json(['error' => 'Password is required'], 400);
            return;
        }

        // Initialize failed attempts if not set
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
        }

        // Re-enable CAPTCHA check after 3 failed attempts
        if ($_SESSION['failed_attempts'] >= 3) {
            if (empty($data->captcha) || $data->captcha !== 'expected-captcha-value') {
                Flight::json(['error' => 'CAPTCHA verification required'], 403);
                return;
            }
        }

        // Fetching user by username or email
        $user = User::findByUsernameOrEmail($data->username, $data->email);
        if (!$user) {
            $_SESSION['failed_attempts']++;
            Flight::json(['error' => 'Invalid username/email or password (User not found)'], 400);
            return;
        }

        // Verifying password
        if (!password_verify($data->password, $user['password'])) {
            $_SESSION['failed_attempts']++;
            Flight::json(['error' => 'Invalid username/email or password (Password mismatch)'], 400);
            return;
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            Flight::json(['error' => 'Please verify your email before logging in'], 400);
            return;
        }

        // Reset failed attempts on successful login
        $_SESSION['failed_attempts'] = 0;

        // Generate a token for the user session (for simplicity, just a message here)
        Flight::json(['message' => 'Login successful!']);
    }
}
