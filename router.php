<?php

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Add a GET route
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a PUT route
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route for any HTTP method
     */
    public function any(string $path, callable $handler): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    /**
     * Add a route with specific method
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $path = $this->basePath . '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';
        
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'pattern' => $this->convertToRegex($path)
        ];
    }

    /**
     * Convert route path to regex pattern for parameter matching
     */
    private function convertToRegex(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch the request to appropriate handler
     */
    public function dispatch(?string $method = null, ?string $uri = null): mixed
    {
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $uri ?? $this->getCurrentUri();

        // Remove query string
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        if (!isset($this->routes[$method])) {
            return $this->handleNotFound();
        }

        foreach ($this->routes[$method] as $path => $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return call_user_func($route['handler'], $params);
            }
        }

        return $this->handleNotFound();
    }

    /**
     * Get current URI
     */
    private function getCurrentUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "404 - Route not found";
    }

    /**
     * Set a custom 404 handler
     */
    // public function setNotFoundHandler(callable $handler): void
    // {
    //     $this->notFoundHandler = $handler;
    // }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}

// Example usage:
/*
$router = new Router();

// Basic routes
$router->get('/', function() {
    return "Welcome to homepage!";
});

$router->get('/about', function() {
    return "About page";
});

// Route with parameters
$router->get('/user/{id}', function($params) {
    return "User ID: " . $params['id'];
});

$router->get('/user/{id}/post/{slug}', function($params) {
    return "User: {$params['id']}, Post: {$params['slug']}";
});

// POST route
$router->post('/contact', function() {
    return "Contact form submitted!";
});

// Dispatch the request
$router->dispatch();
*/