<?php
error_reporting(0);
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer JSON raw y decodificar
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$cedula = $data['cedula'] ?? null;

if (!$cedula) {
    echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    $sql = "UPDATE estudiante e
            JOIN persona p ON e.id_persona = p.id_persona
            SET e.estatus = 'espera'
            WHERE p.cedula = ? AND e.estatus = 'activo'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$cedula]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Estudiante movido a lista de espera correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el estudiante activo con esa cédula o ya está en espera']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
