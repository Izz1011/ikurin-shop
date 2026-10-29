<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    /** @var bool Useful for tests to avoid calling exit() */
    public static bool $exitOnSend = true;

    /**
     * Send a successful JSON response conforming to the API contract.
     */
    public static function ok(mixed $data = null, ?array $meta = null, int $status = 200): void
    {
        $payload = [
            'success' => true,
            'data' => $data ?? (object) [],
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        self::json($payload, $status);
    }

    /**
     * Send a 201 Created JSON response.
     */
    public static function created(mixed $data = null, ?array $meta = null): void
    {
        self::ok($data, $meta, 201);
    }

    /**
     * Send an error JSON response conforming to the API contract.
     */
    public static function error(
        string $code,
        string $message,
        int $status = 400,
        ?array $fields = null
    ): void {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($fields !== null && !empty($fields)) {
            $error['fields'] = $fields;
        }

        $payload = [
            'success' => false,
            'error' => $error,
        ];

        self::json($payload, $status);
    }

    /**
     * Output standard security and JSON headers, encode payload, and output.
     */
    public static function json(array $payload, int $status = 200, array $extraHeaders = []): void
    {
        if (!headers_sent()) {
            http_response_code($status);

            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('Referrer-Policy: strict-origin-when-cross-origin');

            foreach ($extraHeaders as $header => $value) {
                header("{$header}: {$value}");
            }
        }

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (self::$exitOnSend) {
            exit;
        }
    }

    /**
     * Stream a binary file (e.g. private KTP, payment proof) securely.
     */
    public static function streamFile(string $filePath, string $contentType, ?string $downloadName = null): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            self::error('NOT_FOUND', 'Berkas tidak ditemukan.', 404);
            return;
        }

        if (!headers_sent()) {
            http_response_code(200);
            header("Content-Type: {$contentType}");
            header('Content-Length: ' . (string) filesize($filePath));
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('Cache-Control: private, no-cache, no-store, must-revalidate');

            if ($downloadName !== null) {
                header("Content-Disposition: inline; filename=\"{$downloadName}\"");
            } else {
                header('Content-Disposition: inline');
            }
        }

        readfile($filePath);

        if (self::$exitOnSend) {
            exit;
        }
    }
}
