<?php
require_once '../../../BBDD/BBDD.php';
$db = new Database();
$conn = $db->connect();

if (!isset($_GET['id_curso'])) {
    echo json_encode([]);
    exit;
}

$idCurso = $_GET['id_curso'];

// Obtener el profesor asignado a ese curso
$stmt = $conn->prepare("
    SELECT p.id_profesor, per.nombre, per.apellido
    FROM cursos c
    JOIN profesor p ON c.id_profesor = p.id_profesor
    JOIN persona per ON p.id_persona = per.id_persona
    WHERE c.id_cursos = :idCurso
      AND p.status = 'activo'
");
$stmt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
