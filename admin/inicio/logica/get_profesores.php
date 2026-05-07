<?php
require_once '../../../BBDD/BBDD.php';
require_once '../../../verificacion/verificar_acceso.php';

verificarAcceso('administrador');

header('Content-Type: application/json; charset=utf-8');

try {
    $db = new Database();
    $conn = $db->connect();

    // Si viene id_curso, buscamos profesores con especialidades relacionadas
    if (isset($_GET['id_curso']) && !empty($_GET['id_curso'])) {
        $idCurso = intval($_GET['id_curso']);
        
        // Primero obtenemos información del curso
        $stmt = $conn->prepare("
            SELECT nombre_curso, nivel_cursos, descripcion 
            FROM cursos 
            WHERE id_cursos = :idCurso
        ");
        $stmt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmt->execute();
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            // Buscamos profesores que puedan dar este curso basado en especialidad
            // y también profesores que ya han sido asignados a este curso
            $stmt = $conn->prepare("
                SELECT DISTINCT pr.id_profe as id_profesor, p.nombre, p.apellido, pr.especialidad
                FROM profesor pr
                JOIN persona p ON pr.id_persona = p.id_persona
                WHERE pr.status = 'activo'
                AND (
                    -- Profesores con especialidades relacionadas al curso (insensible a mayúsculas/minúsculas)
                    LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nombre_curso)) OR
                    LOWER(TRIM(pr.especialidad)) = LOWER(TRIM(:nivel_curso)) OR
                    LOWER(TRIM(pr.especialidad)) LIKE CONCAT('%', LOWER(TRIM(:nombre_curso)), '%') OR
                    LOWER(TRIM(pr.especialidad)) LIKE CONCAT('%', LOWER(TRIM(:nivel_curso)), '%') OR
                    LOWER(TRIM(:nombre_curso)) LIKE CONCAT('%', LOWER(TRIM(pr.especialidad)), '%') OR
                    LOWER(TRIM(:nivel_curso)) LIKE CONCAT('%', LOWER(TRIM(pr.especialidad)), '%') OR
                    -- Profesores que ya han sido asignados a este curso
                    pr.id_profe IN (
                        SELECT DISTINCT id_profesor 
                        FROM cursos 
                        WHERE id_cursos = :idCurso AND id_profesor IS NOT NULL
                    ) OR
                    -- Si no hay especialidad específica, mostrar todos los activos
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
            
            $nombreCurso = $curso['nombre_curso'];
            $nivelCurso = $curso['nivel_cursos'];
            
            $stmt->bindParam(':nombre_curso', $nombreCurso, PDO::PARAM_STR);
            $stmt->bindParam(':nivel_curso', $nivelCurso, PDO::PARAM_STR);
            $stmt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmt->bindParam(':idCurso2', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log para depuración
            error_log("Curso seleccionado: " . $nombreCurso . " - Nivel: " . $nivelCurso);
            error_log("Profesores encontrados para el curso: " . count($profesores));
        } else {
            $profesores = [];
        }
    } else {
        // Si no hay curso → traigo todos los profesores activos
        $stmt = $conn->query("
            SELECT pr.id_profe as id_profesor, p.nombre, p.apellido, pr.especialidad
            FROM profesor pr
            JOIN persona p ON pr.id_persona = p.id_persona
            WHERE pr.status = 'activo'
            ORDER BY p.nombre, p.apellido
        ");
        $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Log para depuración
    error_log("Total de profesores encontrados: " . count($profesores));
    if (empty($profesores)) {
        error_log("No se encontraron profesores en la base de datos");
    }

    echo json_encode($profesores ?: []);

} catch (Exception $e) {
    error_log("Error en get_profesores.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
