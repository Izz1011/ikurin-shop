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
     * Find user record by email including password hash.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.id, u.username, u.email, u.password, u.phone, u.role, u.is_active, s.status AS store_status
                FROM users u
                LEFT JOIN stores s ON s.user_id = u.id
                WHERE u.email = :email
                LIMIT 1';

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'password' => $row['password'],
            'phone' => $row['phone'],
            'role' => $row['role'],
            'is_active' => (int) $row['is_active'],
            'store_status' => $row['store_status'],
        ];
    }

    /**
     * Check if email is already taken.
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        if ($excludeUserId !== null) {
            $sql = 'SELECT id FROM users WHERE email = :email AND id != :excludeId LIMIT 1';
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute(['email' => $email, 'excludeId' => $excludeUserId]);
        } else {
            $sql = 'SELECT id FROM users WHERE email = :email LIMIT 1';
            $stmt = $this->getDb()->prepare($sql);
            $stmt->execute(['email' => $email]);
        }

        return (bool) $stmt->fetch();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (username, email, password, phone, role, is_active, created_at)
                VALUES (:username, :email, :password, :phone, :role, :is_active, NOW())';

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'USER',
            'is_active' => $data['is_active'] ?? 1,
        ]);

        return (int) $this->getDb()->lastInsertId();
    }

    /**
     * Update user profile (username, phone).
     */
    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('username', $data)) {
            $fields[] = 'username = :username';
            $params['username'] = $data['username'];
        }

        if (array_key_exists('phone', $data)) {
            $fields[] = 'phone = :phone';
            $params['phone'] = $data['phone'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Retrieve the stored password hash by user ID.
     */
    public function getPasswordHash(int $id): ?string
    {
        $sql = 'SELECT password FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? (string) $row['password'] : null;
    }

    /**
     * Update user password.
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $sql = 'UPDATE users SET password = :password WHERE id = :id';
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'password' => $hashedPassword,
        ]);
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
