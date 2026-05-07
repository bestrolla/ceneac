<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$estado = $data['estado'] ?? null;
$motivo = $data['motivo'] ?? null;

if (!$id || !in_array($estado, ['activo', 'inactivo'])) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
  exit;
}

$db = new Database();
$conn = $db->connect();

if ($estado === 'inactivo') {
  $stmt = $conn->prepare("UPDATE salon SET status = ?, motivo = ? WHERE id_salon = ?");
  $success = $stmt->execute([$estado, $motivo, $id]);
}else{
  // Si se activa, se borra el motivo
  $stmt = $conn->prepare("UPDATE salon SET status = ?, motivo =NULL WHERE id_salon = ?");
  $success = $stmt->execute([$estado, $id]);
}

echo json_encode([
  'success' => $success,
  'message' => $success ? 'Estado actualizado correctamente.' : 'Error al actualizar estado.'
]);
