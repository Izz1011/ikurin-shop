<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository
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
     * Find active user with their store status by user ID.
     */
    public function findUserWithStore(int $id): ?array
    {
        $sql = 'SELECT u.id, u.username, u.email, u.phone, u.role, u.is_active, s.status AS store_status
                FROM users u
                LEFT JOIN stores s ON s.user_id = u.id
                WHERE u.id = :id
                LIMIT 1';

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'role' => $row['role'],
            'is_active' => (int) $row['is_active'],
            'store_status' => $row['store_status'],
        ];
    }

    /**
     * Count unread notifications for a user.
     */
    public function countUnreadNotifications(int $userId): int
    {
        $sql = 'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count unread incoming chat messages for a user.
     */
    public function countUnreadMessages(int $userId): int
    {
        $sql = 'SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
