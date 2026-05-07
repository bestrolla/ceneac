<?php
// descartar.php
//Para rechazar la inscripción.
include 'db.php';

if (isset($_POST['id_espera'])) {
    $id_espera = intval($_POST['id_espera']);
    $conn->query("UPDATE lista_espera SET estado='descartado' WHERE id_espera=$id_espera");
// Notificación al admin
	$conn->query("INSERT INTO notificaciones (mensaje, tipo) 
 VALUES ('Un estudiante fue descartado de la lista de espera', 'error')");

    echo "ok";
} else {
    echo "error";
}
