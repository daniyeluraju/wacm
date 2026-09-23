<?php

namespace App\Core;

class Security
{
    /**
     * Generate or retrieve CSRF token
     */
    public static function csrfToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Generate hidden HTML CSRF input field
     */
    public static function csrfField(): string
    {
        $token = self::csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . self::escape($token) . '">';
    }

    /**
     * Verify submitted CSRF token
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $sessionToken = Session::get('_csrf_token');
        if (!$sessionToken) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Regenerate CSRF token (after login or sensitive state changes)
     */
    public static function regenerateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
        return $token;
    }

    /**
     * Escape HTML output (XSS prevention)
     */
    public static function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Generate secure random string (e.g. for unique filenames or tokens)
     */
    public static function randomString(int $length = 32): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }
}
