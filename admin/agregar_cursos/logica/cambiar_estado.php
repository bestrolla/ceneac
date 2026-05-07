<?php
require_once '../../../BBDD/BBDD.php';  

// Obtener JSON POST
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_curso']) || !isset($input['estado'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_curso = (int)$input['id_curso'];
$estado = $input['estado'] === 'activo' ? 'activo' : 'inactivo';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$sql = "UPDATE cursos SET status = ? WHERE id_cursos = ?";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$estado, $id_curso])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
}
