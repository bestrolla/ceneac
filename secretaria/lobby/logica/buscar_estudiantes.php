<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
ini_set('display_errors', 0); // Turn off display_errors for JSON output
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Capture any output that might interfere with JSON
ob_start();

try {
    $db = new Database();
    $conn = $db->connect();

    $q = trim($_GET['q'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $porPagina = 10;

    $sqlFiltro = '';
    $params = [];
    if ($q !== '') {
        $sqlFiltro = " AND (p.nombre LIKE ? OR p.apellido LIKE ? OR p.cedula LIKE ?)";
        $params = ["%$q%", "%$q%", "%$q%"];
    }

    // Contar total de estudiantes activos
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'activo' $sqlFiltro
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    $pages = max(1, ceil($total / $porPagina));
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $porPagina;

    // Obtener datos paginados
    $stmt = $conn->prepare("
        SELECT 
            p.cedula, 
            p.nombre, 
            p.apellido, 
            p.telefono,
            c.id_cursos,
            c.nombre_curso,
            e.id_estudiante
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'activo' $sqlFiltro
        ORDER BY p.nombre ASC
        LIMIT $porPagina OFFSET $offset
    ");
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clear any unwanted output
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'data' => $datos,
        'currentPage' => $page,
        'pages' => $pages,
        'total' => $total
    ]);
} catch (Exception $e) {
    // Clear any unwanted output
    ob_clean();
    
    // Log the error for debugging
    error_log("Lobby search error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

// Ensure output buffer is flushed and ended
if (ob_get_level()) {
    ob_end_flush();
}
?>