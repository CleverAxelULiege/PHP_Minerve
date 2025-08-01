<?php


namespace App\Routing;

class Router
{
    private array $routes = [];
    private string $basePath = "";
    private string $basePathActiveRoute = "";

    public function __construct(string $basePath = "")
    {
        $this->basePath = rtrim($basePath, "/");
    }

    public function get(string $path, callable $handler): void
    {
        $this->addRoute("GET", $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute("POST", $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute("PUT", $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute("DELETE", $path, $handler);
    }

    public function any(string $path, callable $handler): void
    {
        $methods = ["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $path = $this->basePath . "/" . ltrim($path, "/");
        $path = rtrim($path, "/") ?: "/";

        $this->routes[$method][$path] = [
            "handler" => $handler,
            "pattern" => $this->convertToRegex($path)
        ];
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace("/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/", "(?P<$1>[^/]+)", $path);
        return "#^" . $pattern . "$#";
    }

    public function dispatch(?string $method = null, ?string $uri = null): void
    {
        $method = $method ?? $_SERVER["REQUEST_METHOD"] ?? "GET";
        $uri = $uri ?? $this->getCurrentUri();

        $uri = strtok($uri, "?");
        $uri = rtrim($uri, "/") ?: "/";
        $this->basePathActiveRoute = $uri;

        if (!isset($this->routes[$method])) {
            echo $this->handleNotFound();
            return;
        }

        foreach ($this->routes[$method] as $path => $route) {
            if (preg_match($route["pattern"], $uri, $matches)) {
                $params = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
                echo call_user_func($route["handler"], $params);
                return;
            }
        }

        echo $this->handleNotFound();
    }

    private function getCurrentUri(): string
    {
        return $_SERVER["REQUEST_URI"] ?? "/";
    }

    public function getBasePathActiveRoute(): string
    {
        return $this->basePathActiveRoute;
    }

    private function handleNotFound(): string
    {
        http_response_code(404);
        return "404 - Route not found";
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
