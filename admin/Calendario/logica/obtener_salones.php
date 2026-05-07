<?php
// proto/admin/calendario_app/logica/obtener_salones.php

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye la clase de conexión a la base de datos
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// VERIFICACIÓN DE AUTORIZACIÓN - Comentado para permitir acceso sin sesión
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     echo json_encode(['status' => 'error', 'message' => 'No autorizado para ver salones.']);
//     exit();
// }

// Instanciar la clase de base de datos
$db = new Database();
$conn = null; 
try {
    $conn = $db->connect(); 
} catch (Exception $e) {
    error_log("Error de conexión a la base de datos en obtener_salones.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$salones = [];

try {
    // Consulta la tabla 'salones' (plural) y ahora incluye 'estado_salon'
    $stmt = $conn->prepare("SELECT id_salon, nombre_salon, estado_salon FROM salones ORDER BY nombre_salon"); 
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $salones[] = [
            'id_salon' => $row['id_salon'],
            'nombre_salon' => $row['nombre_salon'],
            'estado_salon' => $row['estado_salon'] // Añade el estado del salón
        ];
    }

    echo json_encode(['status' => 'success', 'salones' => $salones]);

} catch (PDOException $e) {
    error_log("Error al obtener salones: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener la lista de salones: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general al obtener salones: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado al obtener la lista de salones.']);
} finally {
    if ($conn) {
        $db->closeConnection();
    }
}
