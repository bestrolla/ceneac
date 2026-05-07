<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

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
    $conn->beginTransaction();

    // Buscar datos relacionados (persona y usuario)
    $stmt = $conn->prepare("SELECT id_persona, id_usuario FROM secretaria WHERE id_secre = ?");
    $stmt->execute([$id_secre]);
    $secre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$secre) {
        echo json_encode(['success' => false, 'message' => 'Secretaria no encontrada']);
        exit;
    }

    $id_persona = $secre['id_persona'];
    $id_usuario = $secre['id_usuario'];

    // Eliminar primero de secretaria
    $stmt = $conn->prepare("DELETE FROM secretaria WHERE id_secre = ?");
    $stmt->execute([$id_secre]);

    // Eliminar usuario si existe
    if ($id_usuario) {
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
    }

    // Eliminar persona si existe
    if ($id_persona) {
        $stmt = $conn->prepare("DELETE FROM persona WHERE id_persona = ?");
        $stmt->execute([$id_persona]);
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Secretaria eliminada permanentemente']);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
