<?php
// Router para requests PHP en Vercel
$requestedPath = $_GET['path'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedPath = strtok($requestedPath, '?');
$requestedPath = '/' . ltrim($requestedPath, '/');

if ($requestedPath === '' || $requestedPath === '/') {
    $requestedPath = '/index.php';
}

$baseDir = realpath(__DIR__ . '/../');
$fullPath = realpath($baseDir . $requestedPath);

if (!$fullPath || strpos($fullPath, $baseDir) !== 0) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

if (is_dir($fullPath)) {
    $indexFile = $fullPath . '/index.php';
    if (file_exists($indexFile)) {
        $fullPath = $indexFile;
    }
}

if (!file_exists($fullPath) || pathinfo($fullPath, PATHINFO_EXTENSION) !== 'php') {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

// Ejecutar el archivo desde su propio directorio base para que los includes relativos funcionen
chdir(dirname($fullPath));
require $fullPath;
