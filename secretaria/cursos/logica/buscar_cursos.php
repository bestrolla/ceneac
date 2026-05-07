<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Capture any output that might interfere with JSON
ob_start();

try {
    $db = new Database();
    $conn = $db->connect();

    $q = trim($_GET['q'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $porPagina = 6; // Menos cursos por página ya que son cards más grandes

    $sqlFiltro = '';
    $params = [];
    if ($q !== '') {
        $sqlFiltro = " WHERE c.nombre_curso LIKE ? AND c.status = 'activo'";
        $params = ["%$q%"];
    } else {
        $sqlFiltro = " WHERE c.status = 'activo'";
    }

    // Contar total de cursos activos
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM cursos c
        $sqlFiltro
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    $pages = max(1, ceil($total / $porPagina));
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $porPagina;

    // Obtener datos paginados con calendario si existe
    $stmt = $conn->prepare("
        SELECT 
            c.id_cursos as id_curso,
            c.nombre_curso,
            c.descripcion,
            c.duracion,
            c.nivel_cursos,
            c.status,
            cal.fecha_inicio,
            cal.fecha_fin,
            cal.horario,
            cal.dias,
            cal.dias_festivo,
            COUNT(DISTINCT e.id_estudiante) as total_estudiantes
        FROM cursos c
        LEFT JOIN calendario cal ON c.id_cursos = cal.id_cursos
        LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'activo'
        $sqlFiltro
        GROUP BY c.id_cursos, cal.id_calendario
        ORDER BY c.nombre_curso ASC
        LIMIT $porPagina OFFSET $offset
    ");
    
    try {
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group courses by id_curso to handle multiple calendar entries
        $cursosGrouped = [];
        foreach ($datos as $curso) {
            $id = $curso['id_curso'];
            if (!isset($cursosGrouped[$id])) {
                $cursosGrouped[$id] = $curso;
            } else {
                // If course has multiple calendar entries, keep the most recent
                if ($curso['fecha_inicio'] && (!$cursosGrouped[$id]['fecha_inicio'] || $curso['fecha_inicio'] > $cursosGrouped[$id]['fecha_inicio'])) {
                    $cursosGrouped[$id] = array_merge($cursosGrouped[$id], [
                        'fecha_inicio' => $curso['fecha_inicio'],
                        'fecha_fin' => $curso['fecha_fin'],
                        'horario' => $curso['horario'],
                        'dias' => $curso['dias'],
                        'dias_festivo' => $curso['dias_festivo']
                    ]);
                }
            }
        }
        
        $datos = array_values($cursosGrouped);
        
    } catch (PDOException $joinError) {
        // Fallback query without calendario if JOIN fails
        error_log("JOIN query failed, using fallback: " . $joinError->getMessage());
        
        $stmt = $conn->prepare("
            SELECT 
                c.id_cursos as id_curso,
                c.nombre_curso,
                c.descripcion,
                c.duracion,
                c.nivel_cursos,
                c.status,
                NULL as fecha_inicio,
                NULL as fecha_fin,
                NULL as horario,
                NULL as dias,
                NULL as dias_festivo,
                COUNT(DISTINCT e.id_estudiante) as total_estudiantes
            FROM cursos c
            LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'activo'
            $sqlFiltro
            GROUP BY c.id_cursos
            ORDER BY c.nombre_curso ASC
            LIMIT $porPagina OFFSET $offset
        ");
        $stmt->execute($params);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
    error_log("Cursos search error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
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