<?php

namespace Solakmirnes\SssdAuth\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailController class for managing email-related actions.
 */
class EmailController {

    /**
     * Send a confirmation email to the user.
     *
     * This method sends an email containing a verification link to the user's email address.
     *
     * @param string $email The user's email address.
     * @param int $userId The user's ID.
     * @return void
     */
    public static function sendConfirmationEmail($email, $userId) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;

            $mail->setFrom('no-reply@example.com', 'Mailer');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $verificationUrl = 'http://localhost:8000/api/verify?user=' . $userId;
            $mail->Body    = "Please click on the following link to verify your email: <a href='$verificationUrl'>Verify Email</a>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
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
    public static function sendPasswordResetEmail($email, $token) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;

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
}
