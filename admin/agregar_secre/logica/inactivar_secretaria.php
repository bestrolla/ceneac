<?php
require_once '../../../BBDD/BBDD.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_secre = $_POST['id_secre'] ?? null;

if (!$id_secre) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("UPDATE secretaria SET status = 'inactivo' WHERE id_secre = ?");
    $stmt->execute([$id_secre]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}
