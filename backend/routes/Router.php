<?php
namespace Routes;

class Router {
    private array $routes = [];

    /**
     * Registers a GET route.
     */
    public function get(string $path, string $handler, array $middlewares = []): void {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Registers a POST route.
     */
    public function post(string $path, string $handler, array $middlewares = []): void {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Registers a PUT route.
     */
    public function put(string $path, string $handler, array $middlewares = []): void {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * Registers a DELETE route.
     */
    public function delete(string $path, string $handler, array $middlewares = []): void {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, string $handler, array $middlewares): void {
        // Convert route template like /api/patients/{id} into pattern: ^/api/patients/(?P<id>[^/]+)$
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Dispatches the incoming request to the matching route handler.
     */
    public function dispatch(string $requestUri, string $requestMethod): void {
        // Normalize request URI (remove query string)
        $parsedUrl = parse_url($requestUri);
        $path = $parsedUrl['path'] ?? '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $path, $matches)) {
                // Filter matches to get named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run Middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($params)) {
                        return; // Stopped by middleware (already sent response)
                    }
                }

                // Call Controller
                list($controllerClass, $method) = explode('@', $route['handler']);
                $controller = new $controllerClass();
                $controller->$method($params);
                return;
            }
        }

        // No route matched
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Endpoint not found.'
        ]);
    }
}
