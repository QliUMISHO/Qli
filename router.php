<?php
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;

if ($uri !== '/' && is_file($path)) {
    return false;
}

$routes = [
    '/' => __DIR__ . '/api/index.php',
    '/about' => __DIR__ . '/api/index.php',
    '/stack' => __DIR__ . '/api/index.php',
    '/contributions' => __DIR__ . '/api/index.php',
    '/repos' => __DIR__ . '/api/index.php',
    '/mal' => __DIR__ . '/api/mal.php',
    '/api/github' => __DIR__ . '/api/github.php',
    '/api/github.php' => __DIR__ . '/api/github.php',
    '/api/mal_proxy' => __DIR__ . '/api/mal_proxy.php',
    '/api/mal_proxy.php' => __DIR__ . '/api/mal_proxy.php',
];

if (isset($routes[$uri])) {
    require $routes[$uri];
    exit;
}

http_response_code(404);
echo 'Not Found';