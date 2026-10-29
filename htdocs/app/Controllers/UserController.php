<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

class UserController
{
    protected AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    /**
     * Ensure the user is logged in and return their user ID.
     */
    protected function requireUserId(): int
    {
        Session::start();
        $userId = Session::get('user_id');

        if (!$userId || !is_numeric($userId)) {
            throw new AppException(
                'UNAUTHENTICATED',
                'Silakan masuk terlebih dahulu.',
                401
            );
        }

        return (int) $userId;
    }

    /**
     * PATCH /api/v1/users/me
     */
    public function updateProfile(Request $request): void
    {
        $userId = $this->requireUserId();
        $updatedUser = $this->authService->updateProfile($userId, $request->getBody());
        Response::ok($updatedUser);
    }

    /**
     * POST /api/v1/users/me/password
     */
    public function updatePassword(Request $request): void
    {
        $userId = $this->requireUserId();
        $this->authService->changePassword($userId, $request->getBody());
        Response::ok((object) []);
    }
}
