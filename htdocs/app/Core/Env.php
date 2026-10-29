<?php

declare(strict_types=1);

namespace App\Core;

class Env
{
    /** @var array<string, mixed> */
    protected static array $variables = [];
    protected static bool $isLoaded = false;

    /**
     * Parse and load environment variables from an .env file.
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // Remove surrounding quotes if present
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            } else {
                // Strip inline comment if not quoted
                $commentPos = strpos($value, '#');
                if ($commentPos !== false) {
                    $value = trim(substr($value, 0, $commentPos));
                }
            }

            $parsedValue = self::castValue($value);

            self::$variables[$key] = $parsedValue;
            $_ENV[$key] = (string) $value;
            $_SERVER[$key] = (string) $value;
            putenv("{$key}={$value}");
        }

        self::$isLoaded = true;
    }

    /**
     * Retrieve an environment variable with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return self::castValue($envVal);
        }

        return $default;
    }

    /**
     * Cast raw string values to boolean, null, or string.
     */
    protected static function castValue(string $value): mixed
    {
        $lower = strtolower($value);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }

        return $value;
    }

    public static function isLoaded(): bool
    {
        return self::$isLoaded;
    }
}
