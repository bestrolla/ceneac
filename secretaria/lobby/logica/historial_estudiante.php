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
    // Consultar el historial
    $sql = "SELECT h.*, c.nombre_curso 
            FROM historial_estudiante h
            JOIN cursos c ON h.id_curso = c.id_cursos
            JOIN persona p ON h.id_persona = p.id_persona
            WHERE p.cedula = ?
            ORDER BY h.fecha_registro DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$cedula]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si hay historial, lo devolvemos
    if ($historial && count($historial) > 0) {
        echo json_encode($historial);
        exit;
    }

    // Si no hay historial, pero la persona existe, devolver como "espera"
    $sql2 = "SELECT e.id_estudiante, p.nombre, p.apellido, p.cedula
             FROM estudiante e
             JOIN persona p ON e.id_persona = p.id_persona
             WHERE p.cedula = ?";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$cedula]);
    $estudiante = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($estudiante) {
        echo json_encode([
            [
                'estatus' => 'espera',
                'nombre' => $estudiante['nombre'],
                'apellido' => $estudiante['apellido'],
                'cedula' => $estudiante['cedula']
            ]
        ]);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => true, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
