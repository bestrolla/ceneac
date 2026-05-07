<?php
require_once '../../../BBDD/BBDD.php';

$db = new Database();
$conn = $db->connect();

$estudiantes = [];

try {
    // Consulta estudiantes con estatus 'espera'
            $sql = "SELECT e.id_estudiante, p.cedula, p.nombre, p.apellido, p.telefono, e.id_curso, c.nombre_curso
                    FROM estudiante e
                    JOIN persona p ON e.id_persona = p.id_persona
                    JOIN cursos c ON e.id_curso = c.id_cursos
                    WHERE e.estatus = 'espera'
                    ORDER BY p.apellido, p.nombre";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    // Opcional: manejar error o loguear
    error_log("Error al cargar estudiantes en espera: " . $e->getMessage());
}
