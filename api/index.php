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

try {
    require $fullPath;
} catch (\Throwable $e) {
    error_log("Error ejecutando " . $fullPath . ": " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    
    // Si la solicitud es JSON (ej. API/AJAX), retornar JSON
    $isJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $isJson = $isJson || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    
    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error interno en el servidor',
            'error' => $e->getMessage()
        ]);
        exit;
    }
    
    // De lo contrario, renderizar un mensaje de error limpio en HTML
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error del Servidor - CENEAC</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
            .error-card { background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 32px; max-width: 500px; width: 100%; border-top: 4px solid #ef4444; }
            h1 { color: #1e293b; font-size: 1.5rem; margin-top: 0; margin-bottom: 12px; }
            p { color: #64748b; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px; }
            .error-details { background: #f1f5f9; padding: 12px 16px; border-radius: 6px; font-family: monospace; font-size: 0.85rem; color: #334155; word-break: break-all; margin-bottom: 24px; }
            .btn { display: inline-block; background-color: #3b82f6; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: background 0.2s; }
            .btn:hover { background-color: #2563eb; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>Error del Servidor</h1>
            <p>Ocurrió un inconveniente procesando su solicitud. Por favor, reintente más tarde o verifique la conexión con el servidor.</p>
            <div class="error-details">
                <?= htmlspecialchars($e->getMessage()) ?>
            </div>
            <a href="javascript:history.back()" class="btn">Regresar</a>
        </div>
    </body>
    </html>
    <?php
}

