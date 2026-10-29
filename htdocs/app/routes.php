<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

/** @var Router $router */

$router->group('/api/v1', function (Router $r): void {
    // Route endpoints will be wired here in BE-003 and subsequent tasks.
    // Placeholder health-check route matching standard GET
    $r->get('/health', function (Request $req): void {
        Response::ok([
            'status' => 'healthy',
            'time' => date('Y-m-d H:i:s'),
        ]);
    });
});
