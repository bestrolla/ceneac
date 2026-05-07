<?php
require_once '../../../BBDD/BBDD.php'; // Ajusta la ruta según tu proyecto

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_salon = $_POST['id_salon'] ?? null;
    $nombre = trim($_POST['nombre']);
    $matricula = (int)$_POST['matricula'];

    if (empty($nombre) || $matricula < 1) {
        // Aquí puedes enviar un error o redirigir con mensaje
        exit('Datos inválidos.');
    }

    try {
        if ($id_salon) {
            // Actualizar salón existente
            $stmt = $conn->prepare("UPDATE salon SET nombre_salon = :nombre, matricula = :matricula WHERE id_salon = :id");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':id', $id_salon);
            $stmt->execute();
        } else {
            // Insertar nuevo salón
            $stmt = $conn->prepare("INSERT INTO salon (nombre_salon, matricula, status) VALUES (:nombre, :matricula, 'activo')");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->execute();
        }
        header("Location: ../vista/lista_salon.php"); // Cambia según tu ruta
        exit;
    } catch (PDOException $e) {
        // Manejo de error (mejor mostrar mensaje amigable o loguear)
        exit("Error al guardar: " . $e->getMessage());
    }
} else {
    exit('Método no permitido');
}
