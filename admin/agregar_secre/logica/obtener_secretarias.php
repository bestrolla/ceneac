<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

$id_secre = $_GET['id'] ?? null;

if (!$id_secre) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT s.id_secre, s.status, s.estado_razon, s.estado_fecha, s.fecha_inicio,
               p.nombre, p.apellido, p.cedula, p.telefono, p.correo
        FROM secretaria s
        JOIN persona p ON s.id_persona = p.id_persona
        WHERE s.id_secre = ?
    ");
    $stmt->execute([$id_secre]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode([
            'success'  => true,
            'id_secre' => $data['id_secre'],
            'nombre'   => $data['nombre'],
            'apellido' => $data['apellido'],
            'cedula'   => $data['cedula'],
            'telefono' => $data['telefono'],
            'correo'   => $data['correo'],
            'status'   => $data['status'],
            'razon'    => $data['estado_razon'] ?? '—',
            'fecha'    => $data['estado_fecha'] ?? '—',
            'fecha_registro' => $data['fecha_inicio'] ?? '—'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Secretaria no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage()]);
}
