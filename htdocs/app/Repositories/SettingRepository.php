<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Throwable;

class SettingRepository
{
    protected ?PDO $db;
    /** @var array<string, string>|null */
    protected static ?array $cache = null;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db;
    }

    protected function getDb(): PDO
    {
        return $this->db ?? Database::getInstance();
    }

    /**
     * Get a setting value by key.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $settings = $this->getAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Get integer setting value.
     */
    public function getInt(string $key, int $default = 0): int
    {
        $val = $this->get($key);
        return $val !== null ? (int) $val : $default;
    }

    /**
     * Retrieve all settings as key-value array with caching.
     */
    public function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $stmt = $this->getDb()->query('SELECT setting_key, setting_value FROM settings');
            $rows = $stmt->fetchAll();

            $result = [];
            foreach ($rows as $row) {
                $result[$row['setting_key']] = $row['setting_value'];
            }

            self::$cache = $result;
            return self::$cache;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Clear cached settings.
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}
