<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ConfigController;
use App\Controllers\UserController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Middleware\CsrfMiddleware;

/** @var Router $router */

$router->group('/api/v1', function (Router $r): void {
    // Health check
    $r->get('/health', function (Request $req): void {
        Response::ok([
            'status' => 'healthy',
            'time' => date('Y-m-d H:i:s'),
        ]);
    });

    // Public config
    $r->get('/config/public', [ConfigController::class, 'publicConfig']);

    // Auth & Session
    $r->get('/auth/csrf', [AuthController::class, 'csrf']);
    $r->get('/auth/me', [AuthController::class, 'me']);
    $r->post('/auth/register', [AuthController::class, 'register']);
    $r->post('/auth/login', [AuthController::class, 'login']);
    $r->post('/auth/logout', [AuthController::class, 'logout']);

    // User Profile & Password
    $r->patch('/users/me', [UserController::class, 'updateProfile']);
    $r->post('/users/me/password', [UserController::class, 'updatePassword']);
}, [CsrfMiddleware::class]);
