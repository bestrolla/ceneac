<?php
// require_once '../clases_logica.php';
require_once '../../../verificacion/verificar_acceso.php';
require_once '../../../BBDD/BBDD.php';

verificarAcceso('administrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();
    
    $id_calendario = $_POST['id_calendario'];
    
    try {
        // Eliminar la programación
        $stmt = $conn->prepare("DELETE FROM calendario WHERE id_calendario = :id");
        $stmt->bindParam(':id', $id_calendario);
        $stmt->execute();
        
        $_SESSION['mensaje'] = "Programación eliminada exitosamente.";
        header("Location: ../vista/inicio.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al eliminar la programación: " . $e->getMessage();
        header("Location: ../vista/inicio.php");
        exit();
    }
}