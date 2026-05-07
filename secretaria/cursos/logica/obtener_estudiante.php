<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if (!isset($_GET['id_curso'])) {
    echo json_encode(['success' => false, 'message' => 'ID del curso no proporcionado']);
    exit;
}

$id_curso = intval($_GET['id_curso']);

try {
    $database = new Database();
    $db = $database->connect();

    // Consulta para obtener estudiantes aprobados en el curso
    $stmt = $db->prepare("
        SELECT p.nombre, p.apellido, p.cedula
        FROM estudiante e
        JOIN persona p ON e.id_persona = p.id_persona
        WHERE e.id_curso = :id_curso AND e.estatus = 'aprobado'
    ");
    $stmt->execute([':id_curso' => $id_curso]);
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'estudiantes' => $estudiantes]);

} catch (PDOException $e) {
    error_log("Error en obtener_estudiantes.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en el servidor']);
}
