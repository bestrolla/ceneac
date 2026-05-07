<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Error: Método no permitido. Se requiere POST.']);
    exit;
}

// Obtener ID del profesor
$id_profe = $_POST['id_profe'] ?? null;

// Validar ID
if (!$id_profe || !is_numeric($id_profe)) {
    echo json_encode(['success' => false, 'message' => 'Error: ID de profesor inválido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();
    

    $conn->beginTransaction();
    

    $stmt = $conn->prepare("SELECT status FROM profesor WHERE id_profe = ?");
    $stmt->execute([$id_profe]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profesor) {
        echo json_encode(['success' => false, 'message' => 'Error: Profesor no encontrado']);
        exit;
    }
    
    if ($profesor['status'] !== 'ausente') {
        echo json_encode([
            'success' => false, 
            'message' => 'Error: El profesor no está marcado como ausente'
        ]);
        exit;
    }
    
    
    $stmt = $conn->prepare("UPDATE profesor SET status = 'activo' WHERE id_profe = ?");
    $stmt->execute([$id_profe]);
    

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profesor reactivado exitosamente'
    ]);
    
} catch (PDOException $e) {
   
    if (isset($conn)) {
        $conn->rollBack();
    }
    
 
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'error_details' => $e->getMessage() 
    ]);
}