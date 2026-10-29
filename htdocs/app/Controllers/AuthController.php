<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController
{
    protected AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    /**
     * GET /api/v1/auth/csrf
     */
    public function csrf(Request $request): void
    {
        $token = Csrf::getToken();
        Response::ok(['csrf_token' => $token]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): void
    {
        $data = $this->authService->getAuthMeData();
        Response::ok($data);
    }
}
