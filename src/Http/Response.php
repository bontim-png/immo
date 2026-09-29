<?php
namespace App\Http;

class Response
{
    private $content = '';
    private $statusCode = 200;
    private $headers = [];
    private $sent = false;

    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function json($data, int $statusCode = 200): self
    {
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->statusCode = $statusCode;
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        return $this;
    }

    public function html(string $html, int $statusCode = 200): self
    {
        $this->content = $html;
        $this->statusCode = $statusCode;
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        return $this;
    }

    public function text(string $text, int $statusCode = 200): self
    {
        $this->content = $text;
        $this->statusCode = $statusCode;
        $this->setHeader('Content-Type', 'text/plain; charset=utf-8');
        return $this;
    }

    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->setHeader('Location', $url);
        $this->statusCode = $statusCode;
        return $this;
    }

    public function send(): void
    {
        if ($this->sent) {
            return;
        }

        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Send content
        echo $this->content;

        $this->sent = true;
    }

    public function __destruct()
    {
        if (!$this->sent) {
            $this->send();
        }
    }

    public static function make($content = '', int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, $headers);
    }
}
