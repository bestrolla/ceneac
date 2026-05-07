<?php
// asignar_curso.php
//Cuando la secretaria asigna un curso a alguien en la lista de espera.
include '../../BBDD/BBDD.php'; // Conexión a la base de datos

if (isset($_POST['id_espera']) && isset($_POST['id_curso'])) {
    $id_espera = intval($_POST['id_espera']);
    $id_curso = intval($_POST['id_curso']);

    // Cambiar estado en lista de espera
    $conn->query("UPDATE lista_espera SET estado='asignado' WHERE id_espera=$id_espera");

    // Aquí deberías también registrar al estudiante en la tabla de inscripciones
    // Ejemplo: INSERT INTO estudiante (id_persona, id_curso, estatus, fecha_inscripcion) ...
    $conn->query("UPDATE lista_espera SET estado='asignado' WHERE id_espera=$id_espera");

	// Notificación al admin
	$conn->query("INSERT INTO notificaciones (mensaje, tipo) 
    VALUES ('Estudiante de lista de espera asignado al curso $id_curso', 'info')");


    echo "ok";
} else {
    echo "error";
}
