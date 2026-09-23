<?php

use App\Core\Env;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\View;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset($configs[$file])) {
            $path = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (file_exists($path)) {
                $configs[$file] = require $path;
            } else {
                $configs[$file] = [];
            }
        }

        $current = $configs[$file];
        foreach ($parts as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        return View::render($view, $data, $layout);
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        return Response::json($data, $statusCode, $headers);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return Security::escape($value);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Security::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Security::csrfField();
    }
}

if (!function_exists('old')) {
    function old(string $key = '', mixed $default = ''): mixed
    {
        return Session::getOldInput($key, $default);
    }
}

if (!function_exists('session')) {
    function session(string $key = '', mixed $default = null): mixed
    {
        if (empty($key)) {
            return Session::get($key, $default);
        }
        return Session::get($key, $default);
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return Session::has('user') && !empty(Session::get('user'));
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        $user = auth_user();
        if (!$user) {
            return false;
        }
        $userRole = $user['role'] ?? 'viewer';
        $roles = (array) $roles;
        return in_array($userRole, $roles, true);
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return app_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
