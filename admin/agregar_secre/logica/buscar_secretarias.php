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

    // Contar total de secretarias activas
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM secretaria s
        JOIN persona p ON s.id_persona = p.id_persona
        WHERE s.status <> 'retirado' $sqlFiltro
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    $pages = max(1, ceil($total / $porPagina));
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $porPagina;

    // Datos
    $stmt = $conn->prepare("
        SELECT s.id_secre, p.nombre, p.apellido, p.cedula, p.telefono, p.correo, s.status
        FROM secretaria s
        JOIN persona p ON s.id_persona = p.id_persona
        WHERE s.status <> 'retirado' $sqlFiltro
        ORDER BY p.nombre ASC
        LIMIT $porPagina OFFSET $offset
    ");
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $datos,
        'currentPage' => $page,
        'pages' => $pages
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
