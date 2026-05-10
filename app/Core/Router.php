<?php

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function add(string $method, string $path, array|callable $handler, array $middleware = []): void
    {
        $this->routes[$method][$this->normalize($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): mixed
    {
        $method = $request->method();
        $path = $this->normalize($request->path());
        $route = $this->routes[$method][$path] ?? null;

        if ($route === null) {
            Response::abort(404, 'Page not found');
        }

        $this->runMiddleware($route['middleware'], $request);

        $handler = $route['handler'];
        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            return (new $class())->{$methodName}($request);
        }

        return $handler($request);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function runMiddleware(array $middleware, Request $request): void
    {
        foreach ($middleware as $name) {
            if ($name === 'auth' && !Session::isAuthenticated()) {
                redirect('/login');
            }

            if ($name === 'guest' && Session::isAuthenticated()) {
                redirect('/dashboard');
            }

            if ($name === 'csrf' && !Csrf::validate((string) $request->input('_token', ''))) {
                Response::abort(419, 'Invalid CSRF token');
            }
        }
    }
}
