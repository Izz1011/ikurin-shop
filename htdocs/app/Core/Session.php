<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    protected static bool $started = false;

    /**
     * Start the session with secure cookie parameters and idle timeout checking.
     */
    public static function start(?int $timeoutMinutes = null): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            self::checkTimeout($timeoutMinutes);
            return;
        }

        if (!headers_sent()) {
            $sessionName = (string) Env::get('SESSION_NAME', 'IKURIN_SID');
            $isSecure = (bool) Env::get('SESSION_SECURE', false);

            @ini_set('session.use_strict_mode', '1');
            @ini_set('session.use_cookies', '1');
            @ini_set('session.use_only_cookies', '1');

            session_name($sessionName);

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        self::$started = true;

        self::checkTimeout($timeoutMinutes);
    }

    /**
     * Verify whether the current session has exceeded the idle timeout.
     */
    protected static function checkTimeout(?int $timeoutMinutes = null): void
    {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $timeout = ($timeoutMinutes ?? (int) Env::get('SESSION_TIMEOUT_MINUTES', 30)) * 60;
        $now = time();

        if (isset($_SESSION['last_activity']) && ($now - (int) $_SESSION['last_activity']) > $timeout) {
            self::destroy();
            throw new AppException(
                'SESSION_EXPIRED',
                'Sesi Anda telah berakhir. Silakan masuk kembali.',
                401
            );
        }

        $_SESSION['last_activity'] = $now;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        self::ensureStarted();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        self::ensureStarted();
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }

        self::$started = false;
    }

    protected static function ensureStarted(): void
    {
        if (!self::$started && session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
    }
}
