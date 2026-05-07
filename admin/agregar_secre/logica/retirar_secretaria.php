<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $id_secre = $_POST['id_secre'] ?? null;
    $razon = trim($_POST['razon'] ?? '');

    if (!$id_secre) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    // Actualizar secretaria → status = 'retirado'
    $stmt = $conn->prepare("
        UPDATE secretaria 
        SET status = 'retirado', 
            razon_retiro = ?, 
            fecha_retiro = NOW()
        WHERE id_secre = ?
    ");
    $stmt->execute([$razon, $id_secre]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
