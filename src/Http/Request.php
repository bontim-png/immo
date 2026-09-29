<?php
namespace App\Http;

class Request
{
    private $method;
    private $uri;
    private $query = [];
    private $body = [];
    private $files = [];
    private $headers = [];
    private $routeParams = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $this->parseUri();
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->files = $_FILES;
        $this->headers = $this->parseHeaders();
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove the base path (/immobilier/public) to get the route path
        $basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Normalize
        $uri = rawurldecode($uri);
        $uri = trim($uri, '/');
        
        return $uri === '' ? '/' : $uri;
    }

    private function parseBody(): array
    {
        $body = [];
        
        if ($this->method === 'GET') {
            return $body;
        }
        
        if ($this->method === 'POST') {
            $body = $_POST;
        }
        
        // Parse JSON body
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $body = array_merge($body, $json);
            }
        }
        
        return $body;
    }

    private function parseHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('HTTP_', '', $key);
                $name = str_replace('_', '-', $name);
                $name = strtolower($name);
                $headers[$name] = $value;
            }
        }
        
        // Add Content-Type and Content-Length
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }
        
        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getQuery(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function getBody(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function getFiles(string $key = null)
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? null;
    }

    public function getHeaders(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->headers;
        }
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    public function getRouteParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isAjax(): bool
    {
        return ($this->headers['x-requested-with'] ?? '') === 'xmlhttprequest';
    }

    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }

    public function getHost(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }

    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function getIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Check for proxy headers
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        return $ip;
    }
}
