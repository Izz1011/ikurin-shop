<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\UserRepository;

class AuthService
{
    protected UserRepository $userRepo;

    public function __construct(?UserRepository $userRepo = null)
    {
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    /**
     * Get the authenticated user data or null if guest / session invalid.
     */
    public function getCurrentUser(): ?array
    {
        Session::start();
        $userId = Session::get('user_id');

        if (!$userId || !is_numeric($userId)) {
            return null;
        }

        $user = $this->userRepo->findUserWithStore((int) $userId);
        if (!$user || $user['is_active'] !== 1) {
            Session::destroy();
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'store_status' => $user['store_status'],
        ];
    }

    /**
     * Assemble payload for GET /auth/me.
     */
    public function getAuthMeData(): array
    {
        $user = $this->getCurrentUser();
        $csrfToken = Csrf::getToken();

        $unreadNotifications = 0;
        $unreadMessages = 0;

        if ($user !== null) {
            $unreadNotifications = $this->userRepo->countUnreadNotifications((int) $user['id']);
            $unreadMessages = $this->userRepo->countUnreadMessages((int) $user['id']);
        }

        return [
            'user' => $user,
            'csrf_token' => $csrfToken,
            'unread_notifications' => $unreadNotifications,
            'unread_messages' => $unreadMessages,
        ];
    }
}
