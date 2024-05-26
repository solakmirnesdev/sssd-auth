<?php

namespace Solakmirnes\SssdAuth;

use PDO;

/**
 * Database class for managing the database connection.
 *
 * This class implements the singleton pattern to ensure that only one instance
 * of the database connection exists. It uses PDO for database interactions.
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
     * Initializes the database connection using the provided credentials
     * from the configuration constants.
     */
    private function __construct() {
        $host = DB_HOST;
        $db = DB_NAME;
        $user = DB_USERNAME;
        $pass = DB_PASSWORD;
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
     * This method ensures that only one instance of the Database class exists.
     * It creates the instance if it doesn't already exist and returns it.
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
     * This method returns the PDO instance for the database connection.
     *
     * @return PDO The PDO connection instance.
     */
    public function getConnection() {
        return $this->pdo;
    }
}
