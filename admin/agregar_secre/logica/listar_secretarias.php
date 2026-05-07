<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_secre = intval($_POST['id_secre'] ?? 0);
$razon = trim($_POST['razon'] ?? '');
$fecha = date('Y-m-d H:i:s');

if ($id_secre <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Actualizar estado a retirado
    $stmt = $conn->prepare("
        UPDATE secretaria 
        SET status = 'retirado', razon = ?, fecha_estado = ? 
        WHERE id_secre = ?
    ");
    $stmt->execute([$razon, $fecha, $id_secre]);

    echo json_encode(['success' => true, 'message' => 'Secretaria retirada correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al retirar: ' . $e->getMessage()]);
}
