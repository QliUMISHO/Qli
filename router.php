<?php
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;

if ($uri !== '/' && is_file($path)) {
    return false;
}

$routes = [
    '/api/github' => __DIR__ . '/api/github.php',
    '/api/github.php' => __DIR__ . '/api/github.php',
    '/api/mal_proxy' => __DIR__ . '/api/mal_proxy.php',
    '/api/mal_proxy.php' => __DIR__ . '/api/mal_proxy.php',
];

if (isset($routes[$uri])) {
    require $routes[$uri];
    exit;
}

require __DIR__ . '/api/index.php';