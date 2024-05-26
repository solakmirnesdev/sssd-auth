<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * PasswordController class for managing password-related actions.
 */
class PasswordController {

    /**
     * Handle password reset request.
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
