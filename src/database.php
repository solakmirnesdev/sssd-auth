<?php

namespace Solakmirnes\SssdAuth;

use PDO;

/**
 * Database class for managing the database connection.
 */
class Database {
    /**
     * @var Database|null Singleton instance of the Database class.
     */
    private static $instance = null;

    /**
     * @var PDO The PDO instance for the database connection.
     */
    private $pdo;

    /**
     * Database constructor.
     *
     * Initializes the database connection using the provided credentials.
     */
    private function __construct() {
        $host = 'localhost';
        $db = 'sssd_auth';
        $user = 'root';
        $pass = 'Supermar!o1';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Get the singleton instance of the Database class.
     *
     * @return Database The singleton instance of the Database class.
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection.
     *
     * @return PDO The PDO connection instance.
     */
    public function getConnection() {
        return $this->pdo;
    }
}
