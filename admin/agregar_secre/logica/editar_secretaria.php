<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_secre  = $_POST['id_secre'] ?? null;
$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$correo    = trim($_POST['correo'] ?? '');

if (!$id_secre || !$nombre || !$apellido || !$telefono || !$correo) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    // Buscar id_persona asociado a esta secretaria
    $stmt = $conn->prepare("SELECT id_persona FROM secretaria WHERE id_secre = ?");
    $stmt->execute([$id_secre]);
    $id_persona = $stmt->fetchColumn();

    if (!$id_persona) {
        echo json_encode(['success' => false, 'message' => 'Secretaria no encontrada']);
        exit;
    }

    // Actualizar datos en persona
    $stmt = $conn->prepare("
        UPDATE persona 
        SET nombre = ?, apellido = ?, telefono = ?, correo = ? 
        WHERE id_persona = ?
    ");
    $stmt->execute([$nombre, $apellido, $telefono, $correo, $id_persona]);

    echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}
