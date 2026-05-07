<?php
// get_lista_espera.php
include 'db.php'; // tu conexión

header('Content-Type: application/json');

$sql = "SELECT le.id_espera, p.nombre, p.apellido, le.fecha_registro, le.estado
        FROM lista_espera le
        INNER JOIN persona p ON le.id_persona = p.id_persona
        WHERE le.estado = 'pendiente'
        ORDER BY le.fecha_registro ASC";

$res = $conn->query($sql);

$espera = [];
while ($row = $res->fetch_assoc()) {
    $espera[] = $row;
}

echo json_encode($espera, JSON_UNESCAPED_UNICODE);
