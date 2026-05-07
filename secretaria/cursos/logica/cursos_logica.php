<?php
class CursosLogica {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function obtenerTodosLosCursos(): array {
        $query = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    c.descripcion,
                    c.duracion,
                    c.nivel_cursos,
                    c.status,
                    cal.fecha_inicio,
                    cal.fecha_fin,
                    cal.horario,
                    cal.dias,
                    cal.dias_festivo,
                    COUNT(e.id_estudiante) AS total_estudiantes
                  FROM cursos c
                  LEFT JOIN calendario cal ON c.id_cursos = cal.id_cursos
                  LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'activo'
                  WHERE c.status = 'activo'
                  GROUP BY c.id_cursos, cal.id_calendario
                  ORDER BY c.nombre_curso ASC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group courses by id_curso to handle multiple calendar entries
            $cursosGrouped = [];
            foreach ($cursos as $curso) {
                $id = $curso['id_curso'];
                if (!isset($cursosGrouped[$id])) {
                    $cursosGrouped[$id] = $curso;
                    $cursosGrouped[$id]['aprobados'] = $this->obtenerEstudiantesAprobados($id);
                } else {
                    // If course has multiple calendar entries, keep the most recent
                    if ($curso['fecha_inicio'] > $cursosGrouped[$id]['fecha_inicio']) {
                        $cursosGrouped[$id] = array_merge($cursosGrouped[$id], [
                            'fecha_inicio' => $curso['fecha_inicio'],
                            'fecha_fin' => $curso['fecha_fin'],
                            'horario' => $curso['horario'],
                            'dias' => $curso['dias'],
                            'dias_festivo' => $curso['dias_festivo']
                        ]);
                    }
                }
            }

            return array_values($cursosGrouped);
        } catch (PDOException $e) {
            error_log("Error al obtener cursos: " . $e->getMessage());
            
            // Fallback query without calendario if JOIN fails
            try {
                $fallbackQuery = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    c.descripcion,
                    c.duracion,
                    c.nivel_cursos,
                    c.status,
                    NULL as fecha_inicio,
                    NULL as fecha_fin,
                    NULL as horario,
                    NULL as dias,
                    NULL as dias_festivo,
                    COUNT(e.id_estudiante) AS total_estudiantes
                  FROM cursos c
                  LEFT JOIN estudiante e ON c.id_cursos = e.id_curso AND e.estatus = 'activo'
                  WHERE c.status = 'activo'
                  GROUP BY c.id_cursos
                  ORDER BY c.nombre_curso ASC";
                  
                $stmt = $this->db->prepare($fallbackQuery);
                $stmt->execute();
                $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($cursos as &$curso) {
                    $curso['aprobados'] = $this->obtenerEstudiantesAprobados($curso['id_curso']);
                }
                
                return $cursos;
            } catch (PDOException $fallbackError) {
                error_log("Fallback query also failed: " . $fallbackError->getMessage());
                throw new Exception("Error al procesar los cursos");
            }
        }
    }

    public function obtenerEstudiantesAprobados(int $id_curso): array {
        $query = "SELECT 
                    e.id_estudiante,
                    p.nombre,
                    p.apellido,
                    p.cedula
                  FROM estudiante e
                  JOIN persona p ON e.id_persona = p.id_persona
                  WHERE e.id_curso = :id_curso AND e.estatus = 'activo'";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_curso' => $id_curso]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estudiantes aprobados: " . $e->getMessage());
            return []; // Return empty array instead of throwing exception
        }
    }

    public function obtenerCursoPorId(int $id_curso): ?array {
        $query = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    c.descripcion,
                    c.duracion,
                    c.nivel_cursos,
                    c.status,
                    cal.fecha_inicio,
                    cal.fecha_fin,
                    cal.horario,
                    cal.dias,
                    cal.dias_festivo
                  FROM cursos c
                  LEFT JOIN calendario cal ON c.id_cursos = cal.id_cursos
                  WHERE c.id_cursos = :id_curso
                  ORDER BY cal.fecha_inicio DESC
                  LIMIT 1";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_curso' => $id_curso]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($curso) {
                $curso['aprobados'] = $this->obtenerEstudiantesAprobados($id_curso);
                return $curso;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error al obtener curso: " . $e->getMessage());
            
            // Fallback query without calendario if JOIN fails
            try {
                $fallbackQuery = "SELECT 
                    c.id_cursos AS id_curso,
                    c.nombre_curso,
                    c.descripcion,
                    c.duracion,
                    c.nivel_cursos,
                    c.status,
                    NULL as fecha_inicio,
                    NULL as fecha_fin,
                    NULL as horario,
                    NULL as dias,
                    NULL as dias_festivo
                  FROM cursos c
                  WHERE c.id_cursos = :id_curso";
                  
                $stmt = $this->db->prepare($fallbackQuery);
                $stmt->execute([':id_curso' => $id_curso]);
                $curso = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($curso) {
                    $curso['aprobados'] = $this->obtenerEstudiantesAprobados($id_curso);
                    return $curso;
                }
                
                return null;
            } catch (PDOException $fallbackError) {
                error_log("Fallback query for course ID also failed: " . $fallbackError->getMessage());
                throw new Exception("Error al obtener el curso");
            }
        }
    }
}
?>
