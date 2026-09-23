<?php

namespace App\Core;

use RuntimeException;

class View
{
    private static string $basePath = '';
    private static ?string $layout = null;
    private static array $sections = [];
    private static ?string $currentSection = null;

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, DIRECTORY_SEPARATOR) . '/';
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        if (empty(self::$basePath)) {
            self::$basePath = dirname(__DIR__, 2) . '/resources/views/';
        }

        $viewPath = self::$basePath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View [{$view}] not found at [{$viewPath}].");
        }

        self::$layout = $layout;
        self::$sections = [];

        // Extract variables to view scope
        extract($data, EXTR_SKIP);

        // Include global helper data
        $flashes = Session::getFlashes();
        $authUser = Session::get('user');
        $csrfToken = Security::csrfToken();

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // If a layout is defined, render the layout with $content
        if (self::$layout) {
            $layoutPath = self::$basePath . str_replace('.', '/', self::$layout) . '.php';
            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout [" . self::$layout . "] not found at [{$layoutPath}].");
            }

            ob_start();
            include $layoutPath;
            return ob_get_clean();
        }

        return $content;
    }

    public static function partial(string $partial, array $data = []): string
    {
        if (empty(self::$basePath)) {
            self::$basePath = dirname(__DIR__, 2) . '/resources/views/';
        }

        $partialPath = self::$basePath . str_replace('.', '/', $partial) . '.php';
        if (!file_exists($partialPath)) {
            return "<!-- Partial [{$partial}] not found -->";
        }

        extract($data, EXTR_SKIP);
        $flashes = Session::getFlashes();
        $authUser = Session::get('user');
        $csrfToken = Security::csrfToken();

        ob_start();
        include $partialPath;
        return ob_get_clean();
    }

    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        if (self::$currentSection === null) {
            throw new RuntimeException("No section started to end.");
        }
        self::$sections[self::$currentSection] = ob_get_clean();
        self::$currentSection = null;
    }

    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }
}
