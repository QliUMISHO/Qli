<?php
declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;

if ($uri !== '/' && is_file($path)) {
    return false;
}

if ($uri === '/api/github') {
    require __DIR__ . '/api/github.php';
    exit;
}

require __DIR__ . '/api/index.php';