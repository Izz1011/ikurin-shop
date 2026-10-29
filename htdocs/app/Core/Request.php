<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    protected string $method;
    protected string $path;
    protected array $headers;
    protected array $queryParams;
    protected array $bodyParams;
    protected array $files;
    protected array $routeParams = [];
    protected ?array $user = null;

    public function __construct(
        ?string $method = null,
        ?string $path = null,
        ?array $queryParams = null,
        ?array $bodyParams = null,
        ?array $headers = null,
        ?array $files = null
    ) {
        $this->method = strtoupper($method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->headers = $headers ?? $this->captureHeaders();
        $this->path = $path ?? $this->capturePath();
        $this->queryParams = $queryParams ?? $_GET;
        $this->files = $files ?? $_FILES;
        $this->bodyParams = $bodyParams ?? $this->captureBody();
    }

    /**
     * Create Request from PHP globals.
     */
    public static function createFromGlobals(): self
    {
        return new self();
    }

    protected function captureHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = (string) $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $name = str_replace('_', '-', strtolower($key));
                $headers[$name] = (string) $value;
            }
        }
        return $headers;
    }

    protected function capturePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // If served from a subfolder, locate /api/v1 if present
        $apiPos = strpos($path, '/api/v1');
        if ($apiPos !== false && $apiPos > 0) {
            $path = substr($path, $apiPos);
        }

        // Normalize trailing slashes
        if (strlen($path) > 1 && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    protected function captureBody(): array
    {
        $contentType = $this->getHeader('content-type', '');

        if ($contentType !== null && str_contains(strtolower($contentType), 'application/json')) {
            $raw = file_get_contents('php://input');
            if ($raw !== false && trim($raw) !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
            return [];
        }

        return $_POST;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        $lower = strtolower($name);
        return $this->headers[$lower] ?? $default;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQuery(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->queryParams;
        }

        return $this->queryParams[$key] ?? $default;
    }

    public function getBody(): array
    {
        return $this->bodyParams;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $default;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setUser(?array $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return isset($this->user['id']) ? (int) $this->user['id'] : null;
    }

    public function getIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
