<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Encabezado JSON
header('Content-Type: application/json');

require_once '../../../BBDD/BBDD.php';

$db = new Database();
$conn = $db->connect();

// Verificar método
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos
    $curso = $_POST['curso'] ?? null;
    $cedula = $_POST['cedula'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $apellido = $_POST['apellido'] ?? null;
    $telefono = $_POST['telefono'] ?? '';

    if (!$curso || !$cedula || !$nombre || !$apellido) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    try {
        // Evitar duplicados
        $stmt = $conn->prepare("SELECT * FROM estudiante WHERE cedula = ? AND id_curso = ?");
        $stmt->execute([$cedula, $curso]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El estudiante ya está registrado en este curso']);
            exit;
        }

        // Insertar
        $stmt = $conn->prepare("INSERT INTO estudiante (cedula, nombre, apellido, telefono, id_curso, estado) VALUES (?, ?, ?, ?, ?, 'espera')");
        $stmt->execute([$cedula, $nombre, $apellido, $telefono, $curso]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
