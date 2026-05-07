<?php
require_once '../../../BBDD/BBDD.php';  
header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id_salon = $data['id_salon'] ?? null;

    if ($id_salon) {
        $status = 'eliminado';
        $motivo = NULL;

        $stmt = $conn->prepare("UPDATE salon SET status = 'eliminado', motivo = ? WHERE id_salon = ?");
        $stmt->bindParam(1, $motivo);
        $stmt->bindParam(2, $id_salon, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Salón eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el salón']);
        }

        $stmt->closeCursor();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de salón no proporcionado']);
    }
}
?>