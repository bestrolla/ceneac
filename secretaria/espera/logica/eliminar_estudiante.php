<?php
require_once '../../../BBDD/BBDD.php';
require_once '../../../controlador/estudiante.php';
require_once '../../../controlador/persona.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer y decodificar el cuerpo JSON
$input = json_decode(file_get_contents("php://input"), true);
$cedula = $input['cedula'] ?? null;

if (!$cedula) {
    echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos']);
    exit;
}

try {
    // Buscar el id_persona por cédula
    $stmt = $conn->prepare("SELECT id_persona FROM persona WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $id_persona = $stmt->fetchColumn();

    if (!$id_persona) {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
        exit;
    }

    // Marcar estudiante como inactivo
    $stmt = $conn->prepare("UPDATE estudiante SET estatus = 'inactivo' WHERE id_persona = ?");
    $success = $stmt->execute([$id_persona]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Estudiante marcado como inactivo' : 'Error al actualizar'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
