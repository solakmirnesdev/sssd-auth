<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use Solakmirnes\SssdAuth\Database;
use Firebase\JWT\JWT;
use Exception;
use OTPHP\TOTP;

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
        $data = Flight::request()->data->getData();

        // Verify Captcha
        if (isset($data['h-captcha-response'])) {
            $captchaResponse = $data['h-captcha-response'];
            $verifyUrl = 'https://hcaptcha.com/siteverify';
            $response = file_get_contents($verifyUrl . '?secret=' . HCAPTCHA_SERVER_SECRET . '&response=' . $captchaResponse);
            $responseData = json_decode($response);
            if (!$responseData->success) {
                Flight::json(['error' => 'Captcha verification failed'], 403);
                return;
            }
        } else {
            Flight::json(['error' => 'Captcha is required'], 403);
            return;
        }

        // Proceed with registration logic...
        if (empty($data['full_name'])) {
            Flight::json(['error' => 'Full name is required'], 400);
            return;
        }

        if (empty($data['username']) || strlen($data['username']) <= 3 || !ctype_alnum($data['username'])) {
            Flight::json(['error' => 'Invalid username'], 400);
            return;
        }

        $reservedNames = ['admin', 'root', 'system'];
        if (in_array(strtolower($data['username']), $reservedNames)) {
            Flight::json(['error' => 'Username is reserved'], 400);
            return;
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            Flight::json(['error' => 'Password must be at least 8 characters long'], 400);
            return;
        }

        if (ValidationController::isPasswordPwned($data['password'])) {
            Flight::json(['error' => 'Password has been compromised in a data breach'], 400);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Flight::json(['error' => 'Invalid email address'], 400);
            return;
        }

        if (!ValidationController::isValidDomainExtension($data['email'])) {
            Flight::json(['error' => 'Invalid email domain extension'], 400);
            return;
        }

        if (!ValidationController::hasValidMXRecords($data['email'])) {
            Flight::json(['error' => 'Email domain does not have valid MX records'], 400);
            return;
        }

        if (!ValidationController::isValidPhoneNumber($data['phone_number'])) {
            Flight::json(['error' => 'Invalid phone number'], 400);
            return;
        }

        if (User::findByUsernameOrEmail($data['username'], $data['email'])) {
            Flight::json(['error' => 'Username or email already exists'], 400);
            return;
        }

        // Create new user and generate 2FA secret
        $userId = User::create($data['full_name'], $data['username'], $data['password'], $data['email'], $data['phone_number']);
        $totp = TOTP::create();
        User::updateTOTPSecret($userId, $totp->getSecret());

        // Send confirmation email
        EmailController::sendConfirmationEmail($data['email'], $userId);

        Flight::json(['message' => 'Registration successful! Please check your email to verify your account.']);
    }

    /**
     * Verify CAPTCHA using the provided response.
     *
     * @param string $captchaResponse The CAPTCHA response from the client.
     * @return bool True if the CAPTCHA is valid, false otherwise.
     */
    private static function verifyCaptcha($captchaResponse) {
        $secret = HCAPTCHA_SERVER_SECRET;
        $response = file_get_contents("https://hcaptcha.com/siteverify?secret={$secret}&response={$captchaResponse}");
        $responseData = json_decode($response);
        return $responseData->success;
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
        session_start(); // Start session to track login attempts

        try {
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
                if (empty($data['h-captcha-response']) || !self::verifyCaptcha($data['h-captcha-response'])) {
                    Flight::json(['error' => 'CAPTCHA verification failed', 'captcha_needed' => true], 403);
                    return;
                }
            }

            $user = User::findByUsernameOrEmail($data->username, $data->email);
            if (!$user) {
                self::logFailedAttempt();
                Flight::json(['error' => 'Invalid username/email or password', 'captcha_needed' => $_SESSION['failed_attempts'] >= 3], 400);
                return;
            }

            if (!password_verify($data->password, $user['password'])) {
                self::logFailedAttempt();
                Flight::json(['error' => 'Invalid username/email or password', 'captcha_needed' => $_SESSION['failed_attempts'] >= 3], 400);
                return;
            }

            if (!$user['email_verified']) {
                Flight::json(['error' => 'Please verify your email before logging in'], 400);
                return;
            }

            $_SESSION['failed_attempts'] = 0;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['2fa_required'] = true;

            Flight::json(['message' => 'First step login successful! Please complete 2FA verification.']);
        } catch (Exception $e) {
            Flight::json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify 2FA code.
     *
     * This method verifies the 2FA code provided by the user.
     * If the code is correct, it completes the login process.
     *
     * @return void
     */
    public static function verify2FA() {
        session_start();

        if (!isset($_SESSION['user_id']) || !$_SESSION['2fa_required']) {
            Flight::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = Flight::request()->data;
        $user = User::findById($_SESSION['user_id']);

        if (empty($data['totp_code']) || !self::verifyTOTP($user['totp_secret'], $data['totp_code'])) {
            Flight::json(['error' => 'Invalid TOTP code'], 400);
            return;
        }

        $_SESSION['2fa_required'] = false;

        $payload = [
            'iss' => "http://localhost",
            'aud' => "http://localhost",
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600,
            'data' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ];

        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');

        Flight::json(['message' => '2FA verification successful!', 'token' => $jwt]);
    }

    /**
     * Log a failed login attempt and increment the failed attempts counter.
     *
     * @return void
     */
    private static function logFailedAttempt() {
        if (!isset($_SESSION['failed_attempts'])) {
            $_SESSION['failed_attempts'] = 0;
        }
        $_SESSION['failed_attempts']++;
    }

    /**
     * Verify TOTP using the provided code.
     *
     * @param string $secret The TOTP secret.
     * @param string $code The TOTP code provided by the user.
     * @return bool True if the TOTP code is valid, false otherwise.
     */
    private static function verifyTOTP($secret, $code) {
        $totp = TOTP::create($secret);
        return $totp->verify($code);
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
        $expiryTime = time() + 300;

        User::savePasswordResetToken($user['id'], $resetToken, $expiryTime);

        EmailController::sendPasswordResetEmail($data->email, $resetToken);

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
