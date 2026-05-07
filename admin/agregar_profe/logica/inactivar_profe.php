<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_profe = $_POST['id_profe'] ?? null;
$estado = $_POST['estado'] ?? null;
$motivo = $_POST['motivo'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
$razon = $_POST['razon'] ?? 'reposo';

// Validar estados permitidos
if (!$id_profe || !is_numeric($id_profe) || !in_array($estado, ['activo', 'ausente', 'inactivo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $conn->beginTransaction();
    
    
    $stmt = $conn->prepare("UPDATE profesor SET status = ?, fecha_actualizacion = NOW() WHERE id_profe = ?");
    $stmt->execute([$estado, $id_profe]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("No se encontró el profesor con ID: $id_profe");
    }
    
 
    if ($estado === 'ausente') {
        $stmt = $conn->prepare("
            UPDATE profesor 
            SET 
                fecha_inicio_ausencia = ?,
                razon_ausencia = ?,
                detalles_ausencia = ?,
                fecha_registro_ausencia = NOW()
            WHERE id_profe = ?
        ");
        $stmt->execute([$fecha_inicio, $razon, $motivo, $id_profe]);
    } 
  
    else if ($estado === 'inactivo') {
        $stmt = $conn->prepare("
            UPDATE profesor 
            SET 
                fecha_inicio_ausencia = NULL,
                razon_ausencia = NULL,
                detalles_ausencia = NULL,
                fecha_registro_ausencia = NULL
            WHERE id_profe = ?
        ");
        $stmt->execute([$id_profe]);
    }
    
    else if ($estado === 'activo') {
        $stmt = $conn->prepare("
            UPDATE profesor 
            SET 
                fecha_inicio_ausencia = NULL,
                razon_ausencia = NULL,
                detalles_ausencia = NULL,
                fecha_registro_ausencia = NULL
            WHERE id_profe = ?
        ");
        $stmt->execute([$id_profe]);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Estado actualizado a: $estado",
        'nuevo_estado' => $estado,
        'motivo' => $estado === 'ausente' ? $motivo : null
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error BD al cambiar estado profesor: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}