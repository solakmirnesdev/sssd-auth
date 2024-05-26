<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use Solakmirnes\SssdAuth\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * UserController class for managing user-related actions.
 */
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

        if (empty($data->full_name)) {
            Flight::json(['error' => 'Full name is required'], 400);
            return;
        }

        if (empty($data->username) || strlen($data->username) <= 3 || !ctype_alnum($data->username)) {
            Flight::json(['error' => 'Invalid username'], 400);
            return;
        }

        $reservedNames = ['admin', 'root', 'system'];
        if (in_array(strtolower($data->username), $reservedNames)) {
            Flight::json(['error' => 'Username is reserved'], 400);
            return;
        }

        if (empty($data->password) || strlen($data->password) < 8) {
            Flight::json(['error' => 'Password must be at least 8 characters long'], 400);
            return;
        }

        if (self::isPasswordPwned($data->password)) {
            Flight::json(['error' => 'Password has been compromised in a data breach'], 400);
            return;
        }

        if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Flight::json(['error' => 'Invalid email address'], 400);
            return;
        }

        if (!self::isValidPhoneNumber($data->phone_number)) {
            Flight::json(['error' => 'Invalid phone number'], 400);
            return;
        }

        if (User::findByUsernameOrEmail($data->username, $data->email)) {
            Flight::json(['error' => 'Username or email already exists'], 400);
            return;
        }

        $userId = User::create($data->full_name, $data->username, $data->password, $data->email, $data->phone_number);

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
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'cb215d12ba5592';
            $mail->Password = '2d778308ff5ab9';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;

            $mail->setFrom('no-reply@example.com', 'Mailer');
            $mail->addAddress($email);

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

        if (empty($data->username) && empty($data->email)) {
            Flight::json(['error' => 'Username or email is required'], 400);
            return;
        }
        if (empty($data->password)) {
            Flight::json(['error' => 'Password is required'], 400);
            return;
        }

        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
        }

        if ($_SESSION['failed_attempts'] >= 3) {
            if (empty($data->captcha) || $data->captcha !== 'expected-captcha-value') {
                Flight::json(['error' => 'CAPTCHA verification required'], 403);
                return;
            }
        }

        $user = User::findByUsernameOrEmail($data->username, $data->email);
        if (!$user) {
            $_SESSION['failed_attempts']++;
            Flight::json(['error' => 'Invalid username/email or password (User not found)'], 400);
            return;
        }

        if (!password_verify($data->password, $user['password'])) {
            $_SESSION['failed_attempts']++;
            Flight::json(['error' => 'Invalid username/email or password (Password mismatch)'], 400);
            return;
        }

        if (!$user['email_verified']) {
            Flight::json(['error' => 'Please verify your email before logging in'], 400);
            return;
        }

        $_SESSION['failed_attempts'] = 0;

        Flight::json(['message' => 'Login successful!']);
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

        if (empty($data->email)) {
            Flight::json(['error' => 'Email is required'], 400);
            return;
        }

        $user = User::findByEmail($data->email);
        if (!$user) {
            Flight::json(['error' => 'Email not found'], 404);
            return;
        }

        $resetToken = bin2hex(random_bytes(16));
        $expiryTime = time() + 300; // 5 minutes expiry

        User::savePasswordResetToken($user['id'], $resetToken, $expiryTime);

        self::sendPasswordResetEmail($data->email, $resetToken);

        Flight::json(['message' => 'Password reset email sent']);
    }

    /**
     * Send a password reset email to the user.
     *
     * This method sends an email containing a password reset link to the user's email address.
     *
     * @param string $email The user's email address.
     * @param string $token The password reset token.
     * @return void
     */
    private static function sendPasswordResetEmail($email, $token) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = 'cb215d12ba5592';
            $mail->Password = '2d778308ff5ab9';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;

            $mail->setFrom('no-reply@example.com', 'Mailer');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body    = "Click the following link to reset your password: <a href='http://localhost:8000/reset-password?token=$token'>Reset Password</a>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
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

        if (empty($data->token)) {
            Flight::json(['error' => 'Token is required'], 400);
            return;
        }
        if (empty($data->new_password)) {
            Flight::json(['error' => 'New password is required'], 400);
            return;
        }

        $user = User::findByResetToken($data->token);
        if (!$user || $user['reset_token_expiry'] < time()) {
            Flight::json(['error' => 'Invalid or expired token'], 400);
            return;
        }

        $newPasswordHash = password_hash($data->new_password, PASSWORD_DEFAULT);
        User::updatePassword($user['id'], $newPasswordHash);
        User::clearResetToken($user['id']);

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
