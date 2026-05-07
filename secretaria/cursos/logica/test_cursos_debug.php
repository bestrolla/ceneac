<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: text/plain');

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "✓ Database connection successful\n\n";
    
    // Test cursos table structure
    echo "=== CURSOS Table Structure ===\n";
    $stmt = $conn->prepare("DESCRIBE cursos");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: " . $col['Field'] . " (Type: " . $col['Type'] . ")\n";
    }
    
    // Test calendario table structure  
    echo "\n=== CALENDARIO Table Structure ===\n";
    $stmt = $conn->prepare("DESCRIBE calendario");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "Column: " . $col['Field'] . " (Type: " . $col['Type'] . ")\n";
    }
    
    // Check sample data
    echo "\n=== Sample CURSOS Data ===\n";
    $stmt = $conn->prepare("SELECT * FROM cursos LIMIT 3");
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cursos as $i => $curso) {
        echo "Course " . ($i+1) . ":\n";
        foreach ($curso as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
        echo "\n";
    }
    
    echo "\n=== Sample CALENDARIO Data ===\n";
    $stmt = $conn->prepare("SELECT * FROM calendario LIMIT 3");
    $stmt->execute();
    $calendarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($calendarios as $i => $cal) {
        echo "Calendar " . ($i+1) . ":\n";
        foreach ($cal as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
        echo "\n";
    }
    
    // Test the problematic JOIN query
    echo "\n=== Testing Current CursosLogica JOIN Query ===\n";
    $problemQuery = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    cal.fecha_inicio,
                    cal.fecha_fin,
                    cal.horario,
                    cal.dias,
                    cal.dias_festivo,
                    COUNT(e.id_estudiante) AS total_estudiantes
                  FROM cursos c
                  LEFT JOIN calendario cal ON c.id_cursos = cal.id_cursos
                  LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'aprobado'
                  GROUP BY c.id_cursos
                  ORDER BY cal.fecha_inicio DESC
                  LIMIT 3";
    
    try {
        $stmt = $conn->prepare($problemQuery);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Query executed successfully. Results: " . count($results) . "\n";
        foreach ($results as $i => $row) {
            echo "Result " . ($i+1) . ":\n";
            foreach ($row as $key => $value) {
                echo "  $key: " . ($value ?? 'NULL') . "\n";
            }
            echo "\n";
        }
    } catch (PDOException $e) {
        echo "❌ Query failed: " . $e->getMessage() . "\n";
    }
    
    // Test simplified query without calendario
    echo "\n=== Testing Simplified Query (cursos only) ===\n";
    $simpleQuery = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    c.descripcion,
                    c.duracion,
                    c.nivel_cursos,
                    c.status,
                    COUNT(e.id_estudiante) AS total_estudiantes
                  FROM cursos c
                  LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'activo'
                  GROUP BY c.id_cursos
                  ORDER BY c.nombre_curso ASC
                  LIMIT 5";
    
    try {
        $stmt = $conn->prepare($simpleQuery);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Simplified query executed successfully. Results: " . count($results) . "\n";
        foreach ($results as $i => $row) {
            echo "Result " . ($i+1) . ":\n";
            foreach ($row as $key => $value) {
                echo "  $key: " . ($value ?? 'NULL') . "\n";
            }
            echo "\n";
        }
    } catch (PDOException $e) {
        echo "❌ Simplified query failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}