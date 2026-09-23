<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array $query;
    private array $body;
    private array $files;
    private array $headers;
    private ?array $json = null;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        // Handle method override (_method POST field or X-HTTP-Method-Override header)
        if ($this->method === 'POST') {
            if (isset($_POST['_method'])) {
                $override = strtoupper($_POST['_method']);
                if (in_array($override, ['PUT', 'PATCH', 'DELETE', 'GET', 'POST'])) {
                    $this->method = $override;
                }
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // Strip script base directory if running in subdirectory (e.g. /wacm/public/...)
        $basePath = dirname($scriptName);
        if ($basePath !== '/' && $basePath !== '\\' && str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        $parsedUrl = parse_url($requestUri);
        $this->path = '/' . trim($parsedUrl['path'] ?? '/', '/');
        $this->uri = $requestUri;
        $this->query = $_GET;
        $this->body = $_POST;
        $this->files = $_FILES;
        $this->headers = $this->extractHeaders();
    }

    private function extractHeaders(): array
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            $all = getallheaders();
            if ($all) {
                foreach ($all as $key => $val) {
                    $headers[strtoupper(str_replace('-', '_', $key))] = $val;
                }
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = substr($key, 5);
                $headers[$headerKey] = $value;
            }
        }

        return $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(string $key = '', mixed $default = null): mixed
    {
        if (empty($key)) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function post(string $key = '', mixed $default = null): mixed
    {
        if (empty($key)) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function input(string $key = '', mixed $default = null): mixed
    {
        $all = array_merge($this->query, $this->body, $this->json() ?? []);
        if (empty($key)) {
            return $all;
        }
        return $all[$key] ?? $default;
    }

    public function json(string $key = '', mixed $default = null): mixed
    {
        if ($this->json === null) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $this->json = is_array($decoded) ? $decoded : [];
        }

        if (empty($key)) {
            return $this->json;
        }
        return $this->json[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        $formatted = strtoupper(str_replace('-', '_', $key));
        return $this->headers[$formatted] ?? $default;
    }

    public function isAjax(): bool
    {
        return ($this->header('X_REQUESTED_WITH') === 'XMLHttpRequest') ||
               str_contains($this->header('ACCEPT', ''), 'application/json');
    }

    public function ip(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
}
