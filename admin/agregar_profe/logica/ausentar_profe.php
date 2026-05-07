<?php
require_once '../../../BBDD/BBDD.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $idProfe = $_GET['id'];
    
    $db = new Database();
    $conn = $db->connect();
    
    try {
        $conn->beginTransaction();
        
      
        $stmt = $conn->prepare("
            UPDATE profesor 
            SET 
                status = 'ausente',
                fecha_inicio_ausencia = NOW(),
                razon_ausencia = 'otra',
                detalles_ausencia = 'Ausencia marcada desde sistema',
                fecha_registro_ausencia = NOW()
            WHERE id_profe = ?
        ");
        $stmt->execute([$idProfe]);
        
    
        $stmtPersona = $conn->prepare("SELECT id_persona FROM profesor WHERE id_profe = ?");
        $stmtPersona->execute([$idProfe]);
        $idPersona = $stmtPersona->fetchColumn();
        
        if (!$idPersona) {
            throw new Exception("Profesor no encontrado");
        }
        
        
        try {
            $stmtInactivacion = $conn->prepare("
                INSERT INTO historial_inactivaciones 
                (id_profe, id_persona, razon, comentario, fecha, estado) 
                VALUES (?, ?, 'ausencia', 'Marcado como ausente', NOW(), 'ausente')
            ");
            $stmtInactivacion->execute([$idProfe, $idPersona]);
        } catch (PDOException $e) {
     
            error_log("Tabla historial_inactivaciones no encontrada, continuando...");
        }
        
        $conn->commit();
        header("Location: ../vista/lista_profe.php?success=1");
        exit();
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log("Error en ausentar_profe.php: " . $e->getMessage());
        header("Location: ../vista/lista_profe.php?error=1");
        exit();
    }
} else {
    header("Location: ../vista/lista_profe.php");
    exit();
}