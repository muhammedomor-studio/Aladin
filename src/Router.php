<?php
namespace App;
class Router {
    private array $routes = [];
    public function get(string $path, callable $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, callable $handler): void { $this->add('POST', $path, $handler); }
    private function add(string $method, string $path, callable $handler): void {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [$method, '#^' . $pattern . '/?$#', $handler];
    }
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base && $base !== '/' && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base)) ?: '/';
        foreach ($this->routes as [$m, $pattern, $handler]) {
            if ($m !== $method || !preg_match($pattern, $uri, $matches)) continue;
            $params = [];
            foreach ($matches as $k=>$v) if (is_string($k)) $params[] = rawurldecode($v);
            call_user_func_array($handler, $params);
        }
        \response_json(['success'=>false,'message'=>'Endpoint not found.'], 404);
    }
}
