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

    /**
     * POST /api/v1/auth/register
     */
    public function register(Request $request): void
    {
        $user = $this->authService->register($request->getBody());
        Response::created($user);
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): void
    {
        $data = $this->authService->login($request->getBody(), $request->getIp());
        Response::ok($data);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): void
    {
        $this->authService->logout();
        Response::ok((object) []);
    }
}
