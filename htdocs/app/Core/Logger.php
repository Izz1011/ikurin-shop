<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class Logger
{
    protected static ?string $logPath = null;

    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
    }

    public static function getLogPath(): string
    {
        if (self::$logPath !== null) {
            return self::$logPath;
        }

        $baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        self::$logPath = $baseDir . DIRECTORY_SEPARATOR . 'app.log';
        return self::$logPath;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        try {
            $date = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
            $entry = sprintf("[%s] [%s] %s%s%s", $date, strtoupper($level), $message, $contextStr, PHP_EOL);

            $filePath = self::getLogPath();
            @file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
        } catch (Throwable) {
            // Fail silently to avoid breaking application execution on logging errors
        }
    }
}
