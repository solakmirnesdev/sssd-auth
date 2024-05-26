<?php

namespace Solakmirnes\SssdAuth\Controllers;

use Flight;
use Solakmirnes\SssdAuth\Models\User;
use Solakmirnes\SssdAuth\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController {
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

    private static function isPasswordPwned($password) {
        return false;
    }

    private static function isValidPhoneNumber($phoneNumber) {
        return true;
    }

    private static function sendConfirmationEmail($email, $userId) {
        $mail = new PHPMailer(true);
        try {
            // Server settings for mailtrap
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

    public static function verifyEmail() {
        $userId = Flight::request()->query['user'];

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        Flight::json(['message' => 'Email verified successfully!']);
    }
}
