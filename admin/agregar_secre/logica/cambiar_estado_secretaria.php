<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_secre = $_POST['id_secre'] ?? null;
$estado   = $_POST['estado'] ?? null;
$razon    = trim($_POST['razon'] ?? '');

if (!$id_secre || !$estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("UPDATE secretaria 
                            SET status = ?, estado_razon = ?, estado_fecha = NOW() 
                            WHERE id_secre = ?");
    $stmt->execute([$estado, $razon, $id_secre]);

    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
