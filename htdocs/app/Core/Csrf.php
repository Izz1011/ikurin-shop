<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    /**
     * Retrieve the current session CSRF token or generate a new one if not set.
     */
    public static function getToken(): string
    {
        Session::start();

        $token = Session::get('csrf_token');
        if (empty($token) || !is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }

        return $token;
    }

    /**
     * Validate an incoming CSRF token against the session token.
     */
    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        Session::start();
        $stored = Session::get('csrf_token');

        if (empty($stored) || !is_string($stored)) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    /**
     * Regenerate the CSRF token.
     */
    public static function regenerate(): string
    {
        Session::start();
        $newToken = bin2hex(random_bytes(32));
        Session::set('csrf_token', $newToken);
        return $newToken;
    }
}
