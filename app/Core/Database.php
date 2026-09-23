<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;

    private function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        $default = $config['default'] ?? 'mysql';
        $dbConfig = $config['connections'][$default] ?? null;

        if (!$dbConfig) {
            throw new RuntimeException("Database connection [{$default}] configuration not found.");
        }

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options'] ?? []
            );
        } catch (PDOException $e) {
            // Log privately and throw a clean exception without leaking credentials
            error_log("Database Connection Error: " . $e->getMessage());
            throw new RuntimeException("Database connection failed. Please ensure MySQL is running and the database exists.");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function connection(): PDO
    {
        return self::getInstance()->getPdo();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public static function beginTransaction(): bool
    {
        return self::connection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::connection()->inTransaction()) {
            return self::connection()->rollBack();
        }
        return false;
    }

    public static function lastInsertId(): string|false
    {
        return self::connection()->lastInsertId();
    }

    /**
     * Check if database connection is alive and working
     */
    public static function testConnection(): array
    {
        try {
            $pdo = self::connection();
            $stmt = $pdo->query('SELECT VERSION() as version, DATABASE() as current_db');
            $row = $stmt->fetch();
            return [
                'status' => true,
                'version' => $row['version'] ?? 'Unknown',
                'database' => $row['current_db'] ?? 'Unknown',
                'message' => 'Connected successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'version' => null,
                'database' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
