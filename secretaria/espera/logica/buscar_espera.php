<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

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

    // Contar total de estudiantes en espera
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        LEFT JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'espera' $sqlFiltro
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
            c.id_cursos as id_curso,
            c.nombre_curso,
            e.id_estudiante
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        LEFT JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'espera' $sqlFiltro
        ORDER BY p.nombre ASC
        LIMIT $porPagina OFFSET $offset
    ");
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $datos,
        'currentPage' => $page,
        'pages' => $pages,
        'total' => $total
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>