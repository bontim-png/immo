<?php
namespace App\Router;

class Route
{
    private $pattern;
    private $handler;
    private $methods = ['GET'];
    private $middleware = [];
    private $name;

    public function __construct(string $pattern, $handler, array $methods = null)
    {
        $this->pattern = $pattern;
        $this->handler = $handler;
        
        if ($methods !== null) {
            $this->methods = array_map('strtoupper', $methods);
        }
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function matches(string $uri, string $method): bool
    {
        if (!in_array(strtoupper($method), $this->methods)) {
            return false;
        }

        return $this->matchPattern($uri);
    }

    public function matchPattern(string $uri): bool
    {
        // Convert pattern to regex
        $regex = $this->patternToRegex($this->pattern);
        return (bool)preg_match($regex, $uri);
    }

    public function extractParams(string $uri): array
    {
        $regex = $this->patternToRegex($this->pattern);
        
        if (preg_match($regex, $uri, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        
        return [];
    }

    private function patternToRegex(string $pattern): string
    {
        // Escape special regex characters except for our placeholders
        $regex = preg_quote($pattern, '#');
        
        // Replace {param} with named capture groups
        $regex = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^/]+)', $regex);
        
        // Replace {param?} with optional named capture groups
        $regex = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\?\\\}/', '(?P<$1>[^/]*)?', $regex);
        
        // Ensure it matches the full string
        return '#^' . $regex . '$#';
    }

    public function withMiddleware(string ...$middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
