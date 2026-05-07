<?php
// proto/admin/calendario_app/logica/obtener_profesores.php

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye la clase de conexión a la base de datos
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// VERIFICACIÓN DE AUTORIZACIÓN - Comentado para permitir acceso sin sesión
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     echo json_encode(['status' => 'error', 'message' => 'No autorizado para ver profesores.']);
//     exit();
// }

// Instanciar la clase de base de datos
$db = new Database();
$conn = null; 
try {
    $conn = $db->connect(); 
} catch (Exception $e) {
    error_log("Error de conexión a la base de datos en obtener_profesores.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$professors = [];

try {
    // Consulta para obtener los profesores (id_persona, nombre, apellido)
    $stmt = $conn->prepare("SELECT p.id_persona, p.nombre, p.apellido 
                            FROM persona p
                            JOIN profesor pr ON p.id_persona = pr.id_persona
                            WHERE pr.status = 'activo'
                            ORDER BY p.nombre, p.apellido");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear los resultados para que el select en el frontend tenga un 'nombre_completo'
    foreach ($results as $row) {
        $professors[] = [
            'id_profesor' => $row['id_persona'],
            'nombre_completo' => $row['nombre'] . ' ' . $row['apellido']
        ];
    }

    echo json_encode(['status' => 'success', 'professors' => $professors]);

} catch (PDOException $e) {
    error_log("Error al obtener profesores: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener la lista de profesores: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general al obtener profesores: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado al obtener la lista de profesores.']);
} finally {
    if ($conn) {
        $db->closeConnection(); // Cerrar la conexión
    }
}
