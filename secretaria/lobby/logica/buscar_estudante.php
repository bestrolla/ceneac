<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['error' => true, 'message' => 'Error al conectar con la base de datos']);
    exit;
}

$cedula = $_GET['cedula'] ?? null;

if (!$cedula) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "
        SELECT 
            p.cedula, 
            p.nombre, 
            p.apellido, 
            p.telefono,
            c.id_cursos,
            c.nombre_curso
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE p.cedula = ? AND e.estatus = 'activo'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$cedula]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($estudiante ?: []);
} catch (PDOException $e) {
    echo json_encode(['error' => true, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
