<?php
require_once '../../../BBDD/BBDD.php';

$db = new Database();
$conn = $db->connect();

try {
    $stmt = $conn->prepare("
        SELECT s.id_secre, s.status, p.nombre, p.apellido, p.cedula, p.telefono, p.correo 
        FROM secretaria s
        JOIN persona p ON p.id_persona = s.id_persona
        WHERE s.status = 'activo'
    ");
    $stmt->execute();
    $secretarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
