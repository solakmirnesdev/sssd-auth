<?php

namespace Solakmirnes\SssdAuth\Models;

use Solakmirnes\SssdAuth\Database;

class User {

    /**
     * Create a new user in the database.
     *
     * This method inserts a new user record into the database with the provided details.
     * It hashes the password before storing it.
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
     * This method retrieves a user record from the database that matches the given username or email.
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
}
