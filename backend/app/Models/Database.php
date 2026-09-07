<?php
namespace App\Models;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Log standard error internally, return generic error message for HIPAA/security
                error_log("Database Connection Error: " . $e->getMessage());
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'An internal database connection error occurred.'
                ]);
                exit();
            }
        }
        return self::$instance;
    }

    /**
     * Executes a query with bound parameters and returns the statement.
     */
    public static function query(string $sql, array $params = []): \PDOStatement {
        $db = self::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Executes a select query and returns all records.
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Executes a select query and returns a single record or false.
     */
    public static function fetch(string $sql, array $params = []) {
        return self::query($sql, $params)->fetch();
    }

    /**
     * Begins a database transaction.
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commits the current transaction.
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Rolls back the current transaction.
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }

    /**
     * Returns the ID of the last inserted row.
     */
    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }
}
