<?php
// get_notificaciones.php
include '../../BBDD/BBDD.php'; // conexión a la base de datos

header('Content-Type: application/json');

// Notificaciones no leídas
$sql = "SELECT id_notificacion, mensaje, tipo, fecha 
        FROM notificaciones 
        WHERE leida = 0 
        ORDER BY fecha DESC";
$res = $conn->query($sql);

$notificaciones = [];
while ($row = $res->fetch_assoc()) {
    $notificaciones[] = $row;
}

// También devolvemos el total
$total = count($notificaciones);

echo json_encode([
    "total" => $total,
    "notificaciones" => $notificaciones
], JSON_UNESCAPED_UNICODE);
