<?php
// proto/admin/calendario_app/logica/delete_event.php

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye la clase de conexión a la base de datos
// Ruta corregida: desde 'admin/calendario_app/logica/' subir TRES niveles (../../../)
// para llegar a 'proto/', y luego entrar en 'BBDD/BBDD.php'
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// VERIFICACIÓN DE AUTORIZACIÓN
// Solo administradores y secretarias pueden eliminar eventos
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || 
    ($_SESSION['nombre_rol'] !== 'administrador' && $_SESSION['nombre_rol'] !== 'secretaria')) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado para eliminar eventos.']);
    exit();
}

// Verifica que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit();
}

// Obtener los datos del cuerpo de la solicitud JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar que se haya proporcionado un ID
if (empty($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID del evento es obligatorio para eliminar.']);
    exit();
}

// Instanciar la clase de base de datos
$db = new Database();
$conn = null; // Inicializar $conn
try {
    $conn = $db->connect(); // Obtener la conexión PDO
} catch (Exception $e) {
    error_log("Error de conexión a la base de datos en delete_event.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

try {
    // Preparar la consulta SQL para eliminar un evento
    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = :id");
    $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se eliminó alguna fila
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Evento eliminado exitosamente.']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No se encontró el evento para eliminar.']);
    }

} catch (PDOException $e) {
    error_log("Error al eliminar evento: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el evento: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general al eliminar evento: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado al eliminar el evento.']);
} finally {
    if ($conn) {
        $db->closeConnection(); // Cerrar la conexión
    }
}
