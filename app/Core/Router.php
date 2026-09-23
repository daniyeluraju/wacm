<?php

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use Closure;
use RuntimeException;

class Router
{
    private array $routes = [];
    private array $currentGroupMiddleware = [];
    private ?string $currentGroupPrefix = null;

    public function get(string $uri, string|array|Closure $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, string|array|Closure $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, string|array|Closure $action, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, string|array|Closure $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group(array $attributes, Closure $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix;
        $previousMiddleware = $this->currentGroupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->currentGroupPrefix = trim($previousPrefix . '/' . trim($attributes['prefix'], '/'), '/');
        }

        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->currentGroupMiddleware = array_merge($this->currentGroupMiddleware, $middleware);
        }

        $callback($this);

        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;
    }

    private function addRoute(string $method, string $uri, string|array|Closure $action, array $middleware = []): self
    {
        if ($this->currentGroupPrefix) {
            $uri = '/' . trim($this->currentGroupPrefix, '/') . '/' . trim($uri, '/');
        }
        $uri = '/' . trim($uri, '/');

        $allMiddleware = array_merge($this->currentGroupMiddleware, $middleware);

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'pattern' => $this->compilePattern($uri),
            'action' => $action,
            'middleware' => $allMiddleware,
        ];

        return $this;
    }

    private function compilePattern(string $uri): string
    {
        // Convert {param} or {param:regex} into named regex groups
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $uri);

        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        $matchedRoute = null;
        $parameters = [];

        foreach ($this->routes as $route) {
            $isMatchingMethod = ($route['method'] === $method) || ($method === 'HEAD' && $route['method'] === 'GET');
            if (!$isMatchingMethod) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                $matchedRoute = $route;
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $parameters[$key] = urldecode($value);
                    }
                }
                break;
            }
        }

        if (!$matchedRoute) {
            $this->handleNotFound($request);
            return;
        }

        // Execute Middleware Pipeline
        foreach ($matchedRoute['middleware'] as $middlewareClass) {
            $middlewareInstance = is_object($middlewareClass) ? $middlewareClass : new $middlewareClass();
            if ($middlewareInstance instanceof MiddlewareInterface) {
                $proceed = $middlewareInstance->handle($request);
                if (!$proceed) {
                    return; // Middleware aborted the request
                }
            }
        }

        // Execute Controller or Closure
        $action = $matchedRoute['action'];
        $args = array_merge([$request], array_values($parameters));

        try {
            if ($action instanceof Closure) {
                $response = call_user_func_array($action, $args);
            } elseif (is_array($action)) {
                [$controllerClass, $methodName] = $action;
                $controller = new $controllerClass();
                $response = call_user_func_array([$controller, $methodName], $args);
            } elseif (is_string($action) && str_contains($action, '@')) {
                [$controllerClass, $methodName] = explode('@', $action);
                $fullClass = str_starts_with($controllerClass, 'App\\') ? $controllerClass : "App\\Controllers\\{$controllerClass}";
                $controller = new $fullClass();
                $response = call_user_func_array([$controller, $methodName], $args);
            } else {
                throw new RuntimeException("Invalid route action specified for {$path}");
            }

            if ($response instanceof Response) {
                $response->send();
            } elseif (is_string($response)) {
                echo $response;
            }
        } catch (\Throwable $e) {
            $this->handleException($request, $e);
        }
    }

    private function handleNotFound(Request $request): void
    {
        http_response_code(404);
        if ($request->isAjax()) {
            Response::json(['error' => 'Route not found', 'path' => $request->path()], 404)->send();
        } else {
            echo View::render('errors/404', ['path' => $request->path()], 'layouts/main');
        }
    }

    private function handleException(Request $request, \Throwable $e): void
    {
        error_log("Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());

        $debug = (bool) env('APP_DEBUG', false);
        http_response_code(500);

        if ($request->isAjax()) {
            Response::json([
                'error' => 'Internal Server Error',
                'message' => $debug ? $e->getMessage() : 'An unexpected error occurred.',
                'trace' => $debug ? $e->getTraceAsString() : null,
            ], 500)->send();
        } else {
            echo View::render('errors/500', [
                'exception' => $e,
                'debug' => $debug,
            ], 'layouts/main');
        }
    }
}
