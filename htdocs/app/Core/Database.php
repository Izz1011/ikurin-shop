<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    protected static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Get the PDO instance (singleton).
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Alias for getInstance().
     */
    public static function pdo(): PDO
    {
        return self::getInstance();
    }

    /**
     * Create a new PDO connection using environment variables.
     */
    protected static function createConnection(): PDO
    {
        $host = (string) Env::get('DB_HOST', '127.0.0.1');
        $port = (string) Env::get('DB_PORT', '3306');
        $dbName = (string) Env::get('DB_NAME', 'ikurin_shop_local');
        $user = (string) Env::get('DB_USER', 'root');
        $pass = (string) Env::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET NAMES utf8mb4, time_zone = '+07:00'");
            return $pdo;
        } catch (PDOException $e) {
            Logger::error('Database connection failed: ' . $e->getMessage(), [
                'host' => $host,
                'port' => $port,
                'db' => $dbName,
            ]);

            throw new AppException(
                'SERVER_ERROR',
                'Koneksi basis data gagal. Silakan coba beberapa saat lagi.',
                500,
                null,
                $e
            );
        }
    }

    /**
     * Set a custom PDO instance (e.g. for testing).
     */
    public static function setInstance(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }

    /**
     * Begin a transaction.
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Rollback the current transaction if active.
     */
    public static function rollBack(): bool
    {
        if (self::getInstance()->inTransaction()) {
            return self::getInstance()->rollBack();
        }

        return false;
    }

    /**
     * Check if a transaction is currently active.
     */
    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }
}
