<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class LoginAttemptRepository
{
    protected ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db;
    }

    protected function getDb(): PDO
    {
        return $this->db ?? Database::getInstance();
    }

    /**
     * Record a failed login attempt.
     */
    public function record(string $email, string $ip): void
    {
        $sql = 'INSERT INTO login_attempts (email, ip, created_at) VALUES (:email, :ip, NOW())';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'ip' => $ip,
        ]);
    }

    /**
     * Count failed login attempts for a specific email or IP within the given time window (minutes).
     */
    public function countRecentAttempts(string $email, string $ip, int $minutes = 15): int
    {
        $sql = 'SELECT COUNT(*) FROM login_attempts
                WHERE (email = :email OR ip = :ip)
                  AND created_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)';

        $stmt = $this->getDb()->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Clear login attempts upon successful authentication.
     */
    public function clear(string $email, string $ip): void
    {
        $sql = 'DELETE FROM login_attempts WHERE email = :email OR ip = :ip';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'ip' => $ip,
        ]);
    }
}
