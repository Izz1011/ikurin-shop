<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

class Router
{
    /** @var array<array{method: string, path: string, regex: ?string, params: array, handler: array|callable, middlewares: array}> */
    protected array $routes = [];
    protected string $prefix = '';
    protected array $groupMiddlewares = [];

    /**
     * Define a group of routes with a common prefix and optional middlewares.
     */
    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->prefix = $previousPrefix . rtrim($prefix, '/');
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function patch(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->add('PATCH', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $path, $handler, $middlewares);
    }

    public function options(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->add('OPTIONS', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, array|callable $handler, array $middlewares = []): void
    {
        $fullPath = $this->prefix . '/' . ltrim($path, '/');
        if (strlen($fullPath) > 1 && str_ends_with($fullPath, '/')) {
            $fullPath = rtrim($fullPath, '/');
        }

        $allMiddlewares = array_merge($this->groupMiddlewares, $middlewares);

        $paramNames = [];
        $regex = null;

        if (str_contains($fullPath, '{')) {
            $regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '(?P<' . $matches[1] . '>[^/]+)';
            }, $fullPath);

            $regex = '#^' . $regex . '$#';
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'regex' => $regex,
            'params' => $paramNames,
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
        ];
    }

    /**
     * Match and dispatch the incoming request.
     */
    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        $methodMatched = false;

        foreach ($this->routes as $route) {
            $matched = false;
            $extractedParams = [];

            if ($route['regex'] !== null) {
                if (preg_match($route['regex'], $path, $matches)) {
                    $matched = true;
                    foreach ($route['params'] as $paramName) {
                        if (isset($matches[$paramName])) {
                            $extractedParams[$paramName] = urldecode($matches[$paramName]);
                        }
                    }
                }
            } else {
                if ($route['path'] === $path) {
                    $matched = true;
                }
            }

            if ($matched) {
                if ($route['method'] === $method) {
                    $request->setRouteParams($extractedParams);
                    $this->runPipeline($request, $route['middlewares'], $route['handler']);
                    return;
                }
                $methodMatched = true;
            }
        }

        if ($methodMatched) {
            throw new AppException('METHOD_NOT_ALLOWED', 'Metode HTTP tidak diizinkan untuk endpoint ini.', 405);
        }

        throw new AppException('NOT_FOUND', 'Endpoint tidak ditemukan.', 404);
    }

    /**
     * Run middlewares in order, followed by the controller/callable handler.
     */
    protected function runPipeline(Request $request, array $middlewares, array|callable $handler): void
    {
        $pipeline = function (Request $req) use ($handler) {
            $this->invokeHandler($handler, $req);
        };

        // Build the middleware pipeline in reverse order
        for ($i = count($middlewares) - 1; $i >= 0; $i--) {
            $middleware = $middlewares[$i];
            $next = $pipeline;

            $pipeline = function (Request $req) use ($middleware, $next) {
                if (is_string($middleware) && class_exists($middleware)) {
                    $instance = new $middleware();
                    if (method_exists($instance, 'handle')) {
                        $instance->handle($req, $next);
                        return;
                    }
                } elseif (is_callable($middleware)) {
                    $middleware($req, $next);
                    return;
                }

                $next($req);
            };
        }

        $pipeline($request);
    }

    /**
     * Invoke the route handler.
     */
    protected function invokeHandler(array|callable $handler, Request $request): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($request);
            return;
        }

        if (is_callable($handler)) {
            $handler($request);
        }
    }
}
