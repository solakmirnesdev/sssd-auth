<?php

namespace Solakmirnes\SssdAuth\Models;

use Solakmirnes\SssdAuth\Database;

/**
 * User model class for managing user data.
 */
class User {
    /**
     * Create a new user in the database.
     *
     * @param string $fullName The full name of the user.
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     * @param string $email The email address of the user.
     * @param string $phoneNumber The phone number of the user.
     * @return int The ID of the newly created user.
     */
    public static function create($fullName, $username, $password, $email, $phoneNumber) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, email, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $username, password_hash($password, PASSWORD_DEFAULT), $email, $phoneNumber]);

        return $pdo->lastInsertId();
    }

    /**
     * Find a user by username or email.
     *
     * @param string|null $username The username of the user.
     * @param string|null $email The email address of the user.
     * @return array|false The user record as an associative array, or false if no user was found.
     */
    public static function findByUsernameOrEmail($username = null, $email = null) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }

    /**
     * Find a user by email.
     *
     * @param string $email The email address of the user.
     * @return array|false The user record as an associative array, or false if no user was found.
     */
    public static function findByEmail($email) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Save a password reset token and expiry time for a user.
     *
     * @param int $userId The ID of the user.
     * @param string $token The password reset token.
     * @param int $expiry The expiry time of the token (in UNIX timestamp format).
     * @return void
     */
    public static function savePasswordResetToken($userId, $token, $expiry) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt->execute([$token, $expiry, $userId]);
    }

    /**
     * Find a user by password reset token.
     *
     * @param string $token The password reset token.
     * @return array|false The user record as an associative array, or false if no user was found.
     */
    public static function findByResetToken($token) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Update a user's password.
     *
     * @param int $userId The ID of the user.
     * @param string $passwordHash The new hashed password.
     * @return void
     */
    public static function updatePassword($userId, $passwordHash) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $userId]);
    }

    /**
     * Clear the password reset token and expiry time for a user.
     *
     * @param int $userId The ID of the user.
     * @return void
     */
    public static function clearResetToken($userId) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
