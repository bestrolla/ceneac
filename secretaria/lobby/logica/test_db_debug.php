<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: text/plain');

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "✓ Database connection successful\n\n";
    
    // Test 1: Check estudiante table structure
    echo "=== Testing estudiante table structure ===\n";
    $stmt = $conn->prepare("DESCRIBE estudiante");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: " . $col['Field'] . " (Type: " . $col['Type'] . ")\n";
    }
    
    echo "\n=== Testing cursos table structure ===\n";
    $stmt = $conn->prepare("DESCRIBE cursos");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: " . $col['Field'] . " (Type: " . $col['Type'] . ")\n";
    }
    
    echo "\n=== Testing persona table structure ===\n";
    $stmt = $conn->prepare("DESCRIBE persona");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: " . $col['Field'] . " (Type: " . $col['Type'] . ")\n";
    }
    
    // Check if there are any students at all
    echo "\n=== Student Statistics ===\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM estudiante");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total students in database: " . $count['total'] . "\n";
    
    // Check different statuses
    $stmt = $conn->prepare("SELECT estatus, COUNT(*) as count FROM estudiante GROUP BY estatus");
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Student statuses:\n";
    foreach ($statuses as $status) {
        echo "  " . $status['estatus'] . ": " . $status['count'] . "\n";
    }
    
    echo "\n=== Testing JOIN query ===\n";
    $sql = "SELECT 
        e.id_estudiante,
        e.id_curso,
        c.id_cursos,
        p.nombre,
        p.apellido,
        p.cedula,
        c.nombre_curso,
        e.estatus
    FROM persona p
    JOIN estudiante e ON p.id_persona = e.id_persona
    JOIN cursos c ON e.id_curso = c.id_cursos
    WHERE e.estatus = 'activo'
    LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($results)) {
        echo "❌ No active students found with JOIN\n";
        
        // Try a simpler query to check what's available
        echo "\n=== Checking available data ===\n";
        $stmt = $conn->prepare("SELECT e.estatus, COUNT(*) as count FROM estudiante e GROUP BY e.estatus");
        $stmt->execute();
        $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($statuses as $status) {
            echo "Status '" . $status['estatus'] . "': " . $status['count'] . " students\n";
        }
        
        // Check if there are any personas
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM persona");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total personas: " . $count['total'] . "\n";
        
        // Check if there are any cursos
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM cursos");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total cursos: " . $count['total'] . "\n";
        
    } else {
        echo "✓ Found " . count($results) . " active students:\n";
        foreach ($results as $student) {
            echo "  ID: " . $student['id_estudiante'] . 
                 ", Name: " . $student['nombre'] . " " . $student['apellido'] .
                 ", Cedula: " . $student['cedula'] .
                 ", Course: " . $student['nombre_curso'] .
                 ", Status: " . $student['estatus'] . "\n";
        }
    }
    
    echo "\n=== Testing exact buscar_estudiantes.php logic ===\n";
    
    $q = '';
    $page = 1;
    $porPagina = 10;

    $sqlFiltro = '';
    $params = [];
    if ($q !== '') {
        $sqlFiltro = " AND (p.nombre LIKE ? OR p.apellido LIKE ? OR p.cedula LIKE ?)";
        $params = ["%$q%", "%$q%", "%$q%"];
    }

    // Contar total de estudiantes activos
    $countSql = "SELECT COUNT(*) 
        FROM persona p
        JOIN estudiante e ON p.id_persona = e.id_persona
        JOIN cursos c ON e.id_curso = c.id_cursos
        WHERE e.estatus = 'activo' $sqlFiltro";
        
    echo "Count SQL: $countSql\n";
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    echo "Total active students: $total\n";

    $pages = max(1, ceil($total / $porPagina));
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $porPagina;

    // Obtener datos paginados
    $dataSql = "SELECT 
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
        WHERE e.estatus = 'activo' $sqlFiltro
        ORDER BY p.nombre ASC
        LIMIT $porPagina OFFSET $offset";
        
    echo "Data SQL: $dataSql\n";
    $stmt = $conn->prepare($dataSql);
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Results: " . count($datos) . " records\n";
    foreach ($datos as $i => $row) {
        echo "  " . ($i+1) . ". " . $row['nombre'] . " " . $row['apellido'] . " (" . $row['cedula'] . ") - " . $row['nombre_curso'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}