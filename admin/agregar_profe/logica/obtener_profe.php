<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("
  SELECT pr.id_profe, pr.status, pr.especialidad, 
         p.nombre, p.apellido, p.cedula, p.telefono, p.correo 
  FROM profesor pr
  JOIN persona p ON p.id_persona = pr.id_persona
  WHERE pr.status = 'activo'
");
$stmt->execute();
$profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $profesores]);
