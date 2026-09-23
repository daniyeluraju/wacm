<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $sessionConfig = $appConfig['session'] ?? [];

        $lifetime = ($sessionConfig['lifetime'] ?? 120) * 60;
        $secure = (bool) ($sessionConfig['secure'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
        $httponly = true;
        $samesite = $sessionConfig['samesite'] ?? 'Lax';

        if (!headers_sent() && php_sapi_name() !== 'cli') {
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite,
            ]);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        self::$started = true;

        // Session timeout and activity tracking
        self::checkTimeout($lifetime);
    }

    private static function checkTimeout(int $lifetime): void
    {
        $now = time();
        $lastActivity = $_SESSION['_last_activity'] ?? $now;

        if (($now - $lastActivity) > $lifetime) {
            self::destroy();
            self::start();
            self::flash('warning', 'Your session has expired due to inactivity. Please log in again.');
        }

        $_SESSION['_last_activity'] = $now;
    }

    public static function regenerate(bool $deleteOldSession = true): bool
    {
        if (!self::$started) {
            self::start();
        }
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent() && php_sapi_name() !== 'cli') {
            return @session_regenerate_id($deleteOldSession);
        }
        return true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$started) {
            self::start();
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        if (!self::$started) {
            self::start();
        }
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        if (!self::$started) {
            self::start();
        }
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (!self::$started) {
            self::start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
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
        session_destroy();
        self::$started = false;
    }

    public static function flash(string $type, string $message): void
    {
        if (!self::$started) {
            self::start();
        }
        if (!isset($_SESSION['_flashes'])) {
            $_SESSION['_flashes'] = [];
        }
        $_SESSION['_flashes'][] = [
            'type' => $type, // success, error, warning, info
            'message' => $message,
        ];
    }

    public static function getFlashes(): array
    {
        if (!self::$started) {
            self::start();
        }
        $flashes = $_SESSION['_flashes'] ?? [];
        unset($_SESSION['_flashes']);
        return $flashes;
    }

    public static function setOldInput(array $data): void
    {
        self::set('_old_input', $data);
    }

    public static function getOldInput(string $key = '', mixed $default = ''): mixed
    {
        $old = self::get('_old_input', []);
        if (empty($key)) {
            return $old;
        }
        return $old[$key] ?? $default;
    }

    public static function clearOldInput(): void
    {
        self::remove('_old_input');
    }
}
