<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function __construct($routes) {
        $this->routes = $routes;
    }
 
    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Dynamically detect base path (e.g., /buddhaword)
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptPath === '/') $scriptPath = '';
        
        if ($scriptPath && strpos($uri, $scriptPath) === 0) {
            $uri = substr($uri, strlen($scriptPath));
        }
        
        $uri = ($uri === '' || $uri === false) ? '/' : $uri;
        
        // Decode URI for matching against routes that might contain Lao/UTF-8 characters
        $decodedUri = urldecode($uri);

        foreach ($this->routes as $route => $action) {
            if ($this->match($route, $decodedUri, $params)) {
                $this->callAction($action, $params);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 Not Found: " . htmlspecialchars($decodedUri) . " (Base: " . htmlspecialchars($scriptPath) . ")";
    }

    protected function match($route, $uri, &$params) {
        // Use the 'u' modifier for unicode support
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = "#^" . $pattern . "$#u";

        if (preg_match($pattern, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    protected function callAction($action, $params) {
        if (is_callable($action)) {
            call_user_func_array($action, $params);
            return;
        }

        list($controller, $method) = explode('@', $action);
        $controller = "App\\Controllers\\" . $controller;
        $instance = new $controller();
        call_user_func_array([$instance, $method], $params);
    }
}
 