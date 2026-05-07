<?php
require_once '../../../BBDD/BBDD.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "<h2>Prueba de filtrado de profesores por curso (Insensible a mayúsculas/minúsculas)</h2>";
    
    // 1. Mostrar todos los cursos disponibles
    echo "<h3>1. Cursos disponibles:</h3>";
    $stmt = $conn->query("SELECT id_cursos, nombre_curso, nivel_cursos FROM cursos WHERE status = 'activo'");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cursos)) {
        echo "<p>No hay cursos activos en la base de datos.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre del Curso</th><th>Nivel</th></tr>";
        foreach ($cursos as $curso) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($curso['id_cursos']) . "</td>";
            echo "<td>" . htmlspecialchars($curso['nombre_curso']) . "</td>";
            echo "<td>" . htmlspecialchars($curso['nivel_cursos']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Mostrar todos los profesores con sus especialidades
    echo "<h3>2. Profesores y sus especialidades:</h3>";
    $stmt = $conn->query("
        SELECT pr.id_profe, p.nombre, p.apellido, pr.especialidad, pr.status
        FROM profesor pr
        JOIN persona p ON pr.id_persona = p.id_persona
        ORDER BY pr.status, p.nombre
    ");
    $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profesores)) {
        echo "<p>No hay profesores en la base de datos.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Especialidad</th><th>Status</th></tr>";
        foreach ($profesores as $prof) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($prof['id_profe']) . "</td>";
            echo "<td>" . htmlspecialchars($prof['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($prof['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($prof['especialidad'] ?? 'Sin especialidad') . "</td>";
            echo "<td>" . htmlspecialchars($prof['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Probar el filtrado para cada curso con la nueva lógica
    if (!empty($cursos)) {
        echo "<h3>3. Prueba de filtrado por curso (Nueva lógica):</h3>";
        
        foreach ($cursos as $curso) {
            echo "<h4>Curso: " . htmlspecialchars($curso['nombre_curso']) . " - Nivel: " . htmlspecialchars($curso['nivel_cursos']) . "</h4>";
            
            $idCurso = $curso['id_cursos'];
            $nombreCurso = $curso['nombre_curso'];
            $nivelCurso = $curso['nivel_cursos'];
            
            // Aplicar la nueva lógica de comparación insensible a mayúsculas/minúsculas
            $stmt = $conn->prepare("
                SELECT DISTINCT pr.id_profe as id_profesor, p.nombre, p.apellido, pr.especialidad
                FROM profesor pr
                JOIN persona p ON pr.id_persona = p.id_persona
                WHERE pr.status = 'activo'
                AND (
                    LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nombre_curso)) OR
                    LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nivel_curso)) OR
                    LOWER(TRIM(pr.especialidad)) LIKE CONCAT('%', LOWER(TRIM(:nombre_curso)), '%') OR
                    LOWER(TRIM(pr.especialidad)) LIKE CONCAT('%', LOWER(TRIM(:nivel_curso)), '%') OR
                    LOWER(TRIM(:nombre_curso)) LIKE CONCAT('%', LOWER(TRIM(pr.especialidad)), '%') OR
                    LOWER(TRIM(:nivel_curso)) LIKE CONCAT('%', LOWER(TRIM(pr.especialidad)), '%') OR
                    pr.id_profe IN (
                        SELECT DISTINCT id_profesor 
                        FROM cursos 
                        WHERE id_cursos = :idCurso AND id_profesor IS NOT NULL
                    ) OR
                    pr.especialidad IS NULL OR TRIM(pr.especialidad) = ''
                )
                ORDER BY 
                    CASE 
                        WHEN pr.id_profe IN (
                            SELECT DISTINCT id_profesor 
                            FROM cursos 
                            WHERE id_cursos = :idCurso2 AND id_profesor IS NOT NULL
                        ) THEN 1
                        WHEN LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nombre_curso)) OR
                             LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nivel_curso)) THEN 2
                        ELSE 3
                    END,
                    p.nombre, p.apellido
            ");
            
            $stmt->bindParam(':nombre_curso', $nombreCurso, PDO::PARAM_STR);
            $stmt->bindParam(':nivel_curso', $nivelCurso, PDO::PARAM_STR);
            $stmt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmt->bindParam(':idCurso2', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            $profesoresFiltrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($profesoresFiltrados)) {
                echo "<p>No se encontraron profesores compatibles para este curso.</p>";
            } else {
                echo "<table border='1' style='margin-bottom: 20px;'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Especialidad</th><th>Prioridad</th></tr>";
                foreach ($profesoresFiltrados as $prof) {
                    // Determinar prioridad
                    $prioridad = "Baja";
                    if (strtolower(trim($prof['especialidad'] ?? '')) === strtolower(trim($nombreCurso)) ||
                        strtolower(trim($prof['especialidad'] ?? '')) === strtolower(trim($nivelCurso))) {
                        $prioridad = "Alta";
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($prof['id_profesor']) . "</td>";
                    echo "<td>" . htmlspecialchars($prof['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($prof['apellido']) . "</td>";
                    echo "<td>" . htmlspecialchars($prof['especialidad'] ?? 'Sin especialidad') . "</td>";
                    echo "<td>" . htmlspecialchars($prioridad) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    }
    
    // 4. Ejemplos de comparaciones
    echo "<h3>4. Ejemplos de comparaciones insensibles a mayúsculas/minúsculas:</h3>";
    echo "<p>Estas comparaciones ahora funcionan correctamente:</p>";
    echo "<ul>";
    echo "<li>'MATEMATICAS' = 'matematicas'</li>";
    echo "<li>'Inglés' = 'INGLÉS'</li>";
    echo "<li>'Programación' = 'programacion'</li>";
    echo "<li>'Nivel 1' = 'nivel 1'</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
