<?php
namespace App\Router;

use App\Http\Request;
use App\Http\Response;
use App\Router\Route;
use Exception;

class Router
{
    private $routes = [];
    private $prefix = '';
    private $middleware = [];
    private $namedRoutes = [];
    private $request = null;

    public function __construct()
    {
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;

        if (isset($attributes['prefix'])) {
            $this->prefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->middleware = array_merge($this->middleware, $middleware);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }

    public function get(string $pattern, $handler): Route
    {
        return $this->addRoute(['GET'], $pattern, $handler);
    }

    public function post(string $pattern, $handler): Route
    {
        return $this->addRoute(['POST'], $pattern, $handler);
    }

    public function put(string $pattern, $handler): Route
    {
        return $this->addRoute(['PUT'], $pattern, $handler);
    }

    public function patch(string $pattern, $handler): Route
    {
        return $this->addRoute(['PATCH'], $pattern, $handler);
    }

    public function delete(string $pattern, $handler): Route
    {
        return $this->addRoute(['DELETE'], $pattern, $handler);
    }

    public function any(string $pattern, $handler): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $pattern, $handler);
    }

    private function addRoute(array $methods, string $pattern, $handler): Route
    {
        $fullPattern = $this->prefix . '/' . trim($pattern, '/');
        $fullPattern = '/' . trim($fullPattern, '/');

        $route = new Route($fullPattern, $handler, $methods);
        
        // Apply group middleware
        if (!empty($this->middleware)) {
            $route->withMiddleware(...$this->middleware);
        }

        $this->routes[] = $route;
        
        return $route;
    }

    public function dispatch(): void
    {
        $request = new \App\Http\Request();
        $uri = $request->getUri();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route->matches($uri, $method)) {
                $params = $route->extractParams($uri);
                $request->setRouteParams($params);
                
                $this->executeRoute($route, $request);
                return;
            }
        }

        // No route matched - 404
        http_response_code(404);
        if ($request->isAjax()) {
            echo json_encode(['error' => trans('not_found')]);
        } else {
            include __DIR__ . '/../../../public/errors/404.php';
        }
        exit;
    }

    private function executeRoute(Route $route, Request $request): void
    {
        $handler = $route->getHandler();
        
        // Execute middleware
        foreach ($route->getMiddleware() as $middleware) {
            if (is_string($middleware)) {
                $middleware = new $middleware();
            }
            
            if (method_exists($middleware, 'handle')) {
                $response = $middleware->handle($request, function() use ($handler, $request) {
                    return $this->executeHandler($handler, $request);
                });
                
                if ($response instanceof Response) {
                    $response->send();
                    return;
                }
            }
        }

        // Execute handler
        $this->executeHandler($handler, $request);
    }

    private function executeHandler($handler, Request $request): void
    {
        if (is_string($handler)) {
            // Controller@method format
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler, 2);
                $controller = new $controller();
                $controller->$method($request);
            }
        } elseif (is_callable($handler)) {
            // Closure or callable
            $handler($request);
        } elseif (is_array($handler)) {
            // [controller, method] format
            list($controller, $method) = $handler;
            $controller = new $controller();
            $controller->$method($request);
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRouteByName(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        return null;
    }

    public function urlFor(string $name, array $params = []): string
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            throw new Exception("Route '$name' not found");
        }

        $pattern = $route->getPattern();
        
        // Replace named parameters
        foreach ($params as $key => $value) {
            $pattern = str_replace('{' . $key . '}', $value, $pattern);
            $pattern = str_replace('{' . $key . '?}', $value, $pattern);
        }

        // Remove optional parameters that weren't provided
        $pattern = preg_replace('/\{[^}]+\?\}/', '', $pattern);

        return base_url() . $pattern;
    }
}
