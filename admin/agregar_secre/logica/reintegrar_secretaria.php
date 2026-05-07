<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $id_secre = $_POST['id_secre'] ?? null;

    if (!$id_secre) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE secretaria 
        SET status = 'activo', 
            razon_retiro = NULL, 
            fecha_retiro = NULL
        WHERE id_secre = ?
    ");
    $stmt->execute([$id_secre]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
