<?php

namespace Solakmirnes\SssdAuth\Models;

use Database;

class User {
    public static function create($fullName, $username, $password, $email, $phoneNumber) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, email, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $username, password_hash($password, PASSWORD_DEFAULT), $email, $phoneNumber]);
        return $pdo->lastInsertId();
    }

    public static function findByUsernameOrEmail($username, $email) {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }
}
