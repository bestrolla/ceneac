<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

try {
    // Test database connection
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception("No database connection");
    }
    
    // Test simple query to check table structure
    $stmt = $conn->prepare("SHOW TABLES LIKE 'estudiante'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        throw new Exception("Table 'estudiante' does not exist");
    }
    
    // Test simple count query
    $stmt = $conn->prepare("SELECT COUNT(*) FROM estudiante WHERE estatus = 'activo'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    // Test the full query with just one record
    $stmt = $conn->prepare("
        SELECT 
            p.cedula, 
            p.nombre, 
            p.apellido, 
            p.telefono,
            c.id_cursos,
            c.nombre_curso,
            e.id_estudiante
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'activo'
        LIMIT 1
    ");
    $stmt->execute();
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database test successful',
        'activeStudentsCount' => $count,
        'sampleStudent' => $sample,
        'tableExists' => $tableExists ? true : false
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database test failed: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>