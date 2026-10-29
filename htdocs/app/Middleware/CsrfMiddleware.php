<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\AppException;
use App\Core\Csrf;
use App\Core\Request;

class CsrfMiddleware
{
    /** @var array<string> Routes exempted from CSRF check (e.g. pusher-auth) */
    protected array $exemptRoutes = [
        '/api/v1/chat/pusher-auth',
    ];

    public function handle(Request $request, callable $next): void
    {
        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PATCH', 'DELETE', 'PUT'], true)) {
            $path = $request->getPath();

            if (!in_array($path, $this->exemptRoutes, true)) {
                $token = $request->getHeader('x-csrf-token');

                if (empty($token) || !Csrf::validate($token)) {
                    throw new AppException(
                        'CSRF_INVALID',
                        'Token CSRF tidak valid atau telah kedaluwarsa.',
                        403
                    );
                }
            }
        }

        $next($request);
    }
}
