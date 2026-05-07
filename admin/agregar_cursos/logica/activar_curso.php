<?php
// Mismo esquema que arriba, solo cambia status a 'activo'
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id_curso'])) {
    echo json_encode(['success' => false, 'message' => 'ID de curso faltante']);
    exit;
}

$id_curso = intval($_POST['id_curso']);

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base']);
    exit;
}

$stmt = $conn->prepare("UPDATE cursos SET status = 'activo' WHERE id_cursos = ?");
if ($stmt->execute([$id_curso])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el curso']);
}
