<?php
require_once '../../../controlador/persona.php';
require_once '../../../controlador/estudiante.php';
require_once '../../../BBDD/BBDD.php';


$db = new Database();
$conn = $db->connect();

function obtenerCursos($conn) {
    $stmt = $conn->prepare("SELECT id_cursos, nombre_curso FROM cursos WHERE status = 'activo' ORDER BY nombre_curso");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerEstudiantes($conn) {
    $sql = "
        SELECT 
            p.cedula, p.nombre, p.apellido, p.telefono, 
            c.nombre_curso
        FROM estudiante e
        INNER JOIN persona p ON e.id_persona = p.id_persona
        INNER JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'activo'
        ORDER BY p.nombre
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$cursos = obtenerCursos($conn);
$estudiantes = obtenerEstudiantes($conn);

// Manejo del POST para agregar estudiante - opcional si lo haces via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí podría ir lógica para agregar estudiante si usas submit tradicional
    // Pero en tu caso lo manejas con JS y fetch, así que lo dejamos vacío o fuera de aquí
}
