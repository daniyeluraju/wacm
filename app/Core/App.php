<?php

namespace App\Core;

class App
{
    private static Router $router;
    private static string $basePath;

    public static function bootstrap(string $basePath): void
    {
        self::$basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        // Load Environment
        Env::load(self::$basePath . '/.env');

        // Set Timezone
        $timezone = env('APP_TIMEZONE', 'UTC');
        date_default_timezone_set($timezone);

        // Configure Error Reporting based on debug setting
        $debug = (bool) env('APP_DEBUG', false);
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', self::$basePath . '/storage/logs/app.log');
        }

        // Start Secure Session
        Session::start();

        // Initialize Views Path
        View::setBasePath(self::$basePath . '/resources/views');

        // Instantiate Router
        self::$router = new Router();

        // Load Routes
        $routesPath = self::$basePath . '/routes/web.php';
        if (file_exists($routesPath)) {
            $router = self::$router;
            require_once $routesPath;
        }
    }

    public static function run(): void
    {
        $request = new Request();
        self::$router->dispatch($request);
    }

    public static function getRouter(): Router
    {
        return self::$router;
    }

    public static function basePath(string $path = ''): string
    {
        return self::$basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}
