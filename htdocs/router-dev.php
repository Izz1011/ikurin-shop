<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$filePath = __DIR__ . $uri;

// Block direct access to sensitive directories
if (
    str_starts_with($uri, '/app') ||
    str_starts_with($uri, '/config') ||
    str_starts_with($uri, '/storage')
) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'FORBIDDEN',
            'message' => 'Akses ditolak.',
        ],
    ]);
    exit;
}

// Route API requests to api/index.php
if (str_starts_with($uri, '/api/')) {
    require __DIR__ . '/api/index.php';
    exit;
}

// Serve existing static files
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Default fallback to index.html if exists
if (file_exists(__DIR__ . '/index.html')) {
    require __DIR__ . '/index.html';
    exit;
}

// Not found
http_response_code(404);
echo 'Not Found';
