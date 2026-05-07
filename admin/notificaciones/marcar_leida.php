<?php
// marcar_leida.php
include 'db.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = $id");
    echo "ok";
} else {
    echo "error";
}
