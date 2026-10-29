<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\AppException;
use App\Core\Env;
use App\Core\Logger;
use App\Core\Response;

Autoloader::register();
Autoloader::addNamespace('App', __DIR__);

// Load environment configuration
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    Env::load($envPath);
}

// Error reporting configuration
$appEnv = (string) Env::get('APP_ENV', 'local');
if ($appEnv === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// Global Exception Handler
set_exception_handler(function (Throwable $e): void {
    if ($e instanceof AppException) {
        Response::error(
            $e->getErrorCode(),
            $e->getMessage(),
            $e->getHttpStatus(),
            $e->getFields()
        );
        return;
    }

    Logger::error('Unhandled exception: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);

    $appEnv = (string) Env::get('APP_ENV', 'local');
    $message = 'Terjadi kesalahan internal pada server.';
    $fields = null;

    if ($appEnv === 'local') {
        $fields = [
            'debug_message' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ];
    }

    Response::error('SERVER_ERROR', $message, 500, $fields);
});

// Global Error Handler (converts warnings/notices to ErrorException)
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting or suppressed with @
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Global Fatal Shutdown Handler
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        Logger::error('Fatal error caught in shutdown: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
        ]);

        if (!headers_sent()) {
            Response::error('SERVER_ERROR', 'Terjadi kesalahan fatal pada server.', 500);
        }
    }
});
