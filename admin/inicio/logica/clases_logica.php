<?php
require_once '../../../BBDD/BBDD.php';

class CursoLogica {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtiene todos los cursos activos
     */
    public function obtenerCursosActivos() {
        $conn = $this->db->connect();
        try {
            $stmt = $conn->prepare("SELECT id_cursos, nombre_curso, nivel_cursos, descripcion, duracion, status 
                                   FROM cursos 
                                   WHERE status = 'activo'");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener cursos activos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Programa un nuevo curso calculando la fecha fin según días de duración y frecuencia de clases
     *
     * @param int $id_curso
     * @param string $dias - días de clase separados por coma, ej: "Lunes,Miércoles,Viernes"
     * @param string $horario - horario en formato "09:00 - 13:00"
     * @param string $fecha_inicio - fecha inicio en formato 'Y-m-d'
     * @param int $duracion_dias - duración en días de clase
     * @return int último id insertado
     * @throws Exception en caso de error o programación duplicada
     */
    public function programarCurso($id_curso, $dias, $horario, $fecha_inicio, $duracion_dias) {
        $conn = $this->db->connect();
        
        try {
            // Verificar si ya existe una programación similar
            $stmt = $conn->prepare("SELECT id_calendario FROM calendario 
                                  WHERE id_cursos = :id_curso 
                                  AND dias = :dias 
                                  AND horario = :horario");
            $stmt->bindParam(':id_curso', $id_curso);
            $stmt->bindParam(':dias', $dias);
            $stmt->bindParam(':horario', $horario);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                throw new Exception("Ya existe una programación idéntica para este curso");
            }
            
            // Calcular fecha fin considerando los días de clase
            $fecha_fin = $this->calcularFechaFin($fecha_inicio, $duracion_dias, $dias);
            
            // Insertar nueva programación
            $stmt = $conn->prepare("INSERT INTO calendario 
                                  (id_cursos, dias, horario, fecha_inicio, fecha_fin) 
                                  VALUES (:id_curso, :dias, :horario, :fecha_inicio, :fecha_fin)");
            
            $stmt->bindParam(':id_curso', $id_curso);
            $stmt->bindParam(':dias', $dias);
            $stmt->bindParam(':horario', $horario);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta");
            }
            
            return $conn->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error PDO al programar curso: " . $e->getMessage());
            throw new Exception("Error al programar el curso en la base de datos");
        }
    }

    /**
     * Calcula la fecha fin basada en días de duración y días de clase
     * 
     * @param string $fecha_inicio Formato 'Y-m-d'
     * @param int $duracion_dias Cantidad de días de clase que dura el curso
     * @param string $dias_clase Cadena con días separados por coma (ej. "Lunes,Miércoles,Viernes")
     * @return string Fecha fin en formato 'Y-m-d'
     * @throws Exception si los parámetros son inválidos
     */
    private function calcularFechaFin($fecha_inicio, $duracion_dias, $dias_clase) {
        // Validar parámetros
        if (empty($fecha_inicio) || empty($duracion_dias) || empty($dias_clase)) {
            throw new Exception("Todos los parámetros son requeridos para calcular la fecha fin");
        }
        
        if (!is_numeric($duracion_dias) || $duracion_dias <= 0) {
            throw new Exception("La duración debe ser un número mayor a 0");
        }
        
        // Validar formato de fecha
        $fecha_test = DateTime::createFromFormat('Y-m-d', $fecha_inicio);
        if (!$fecha_test) {
            throw new Exception("Formato de fecha inválido. Use YYYY-MM-DD");
        }
        
        $dias_semana = explode(',', $dias_clase);
        if (empty($dias_semana)) {
            throw new Exception("Debe especificar al menos un día de clase");
        }
        
        $fecha_actual = new DateTime($fecha_inicio);
        $dias_asignados = 0;
        $max_iteraciones = $duracion_dias * 10; // Protección contra bucle infinito
        $iteraciones = 0;

        while ($dias_asignados < $duracion_dias && $iteraciones < $max_iteraciones) {
            foreach ($dias_semana as $dia) {
                $dia_semana = $this->traducirDia($dia);
                $fecha_actual = $this->encontrarProximoDia($fecha_actual, $dia_semana);

                $dias_asignados++;
                if ($dias_asignados >= $duracion_dias) {
                    break 2;
                }
                // Mover fecha_actual +1 día para continuar en siguiente iteración
                $fecha_actual->modify('+1 day');
            }
            $iteraciones++;
        }
        
        if ($iteraciones >= $max_iteraciones) {
            throw new Exception("Error en el cálculo de fecha fin: demasiadas iteraciones");
        }

        return $fecha_actual->format('Y-m-d');
    }

    /**
     * Traduce un día de la semana en español a su equivalente en inglés para DateTime
     * @param string $dia Día en español (ej. "Lunes")
     * @return string Día en inglés (ej. "Monday")
     */
    private function traducirDia($dia) {
        $dias = [
            'Lunes' => 'Monday',
            'Martes' => 'Tuesday',
            'Miércoles' => 'Wednesday',
            'Jueves' => 'Thursday',
            'Viernes' => 'Friday',
            'Sábado' => 'Saturday',
            'Domingo' => 'Sunday',
        ];
        return $dias[trim($dia)] ?? 'Monday';
    }

    /**
     * Encuentra el próximo día específico a partir de una fecha (incluye la fecha si coincide)
     * 
     * @param DateTime $fecha Fecha inicial
     * @param string $dia_semana Día en inglés (ej. "Monday")
     * @return DateTime Próximo día coincidente
     */
    private function encontrarProximoDia(DateTime $fecha, $dia_semana) {
        $fecha_tmp = clone $fecha;
        $intento = 0;
        while ($fecha_tmp->format('l') !== $dia_semana) {
            $fecha_tmp->modify('+1 day');
            $intento++;
            if ($intento > 7) break; // protección ante error lógico
        }
        return $fecha_tmp;
    }

    /**
     * Obtiene todos los cursos programados ordenados por fecha inicio descendente
     * @return array
     */
    public function obtenerCursosProgramados() {
        $conn = $this->db->connect();
        try {
            $stmt = $conn->prepare("SELECT c.id_calendario, cu.nombre_curso, cu.nivel_cursos, 
                                  c.dias, c.horario, c.fecha_inicio, c.fecha_fin
                                  FROM calendario c
                                  JOIN cursos cu ON c.id_cursos = cu.id_cursos
                                  ORDER BY c.fecha_inicio DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener cursos programados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina una programación de curso dado su id_calendario
     * @param int $id_calendario
     * @return bool
     * @throws Exception
     */
    public function eliminarProgramacion($id_calendario) {
        $conn = $this->db->connect();
        try {
            $stmt = $conn->prepare("DELETE FROM calendario WHERE id_calendario = :id");
            $stmt->bindParam(':id', $id_calendario);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar programación: " . $e->getMessage());
            throw new Exception("Error al eliminar la programación");
        }
    }

    /**
     * Retorna los horarios predefinidos disponibles para la programación
     * @return array
     */
    public function obtenerHorariosDisponibles() {
        return [
            ["Lunes,Miércoles,Viernes", "Mañana", "09:00 - 13:00"],
            ["Lunes,Miércoles,Viernes", "Tarde", "13:00 - 17:00"],
            ["Martes,Jueves", "Mañana", "09:00 - 13:00"],
            ["Martes,Jueves", "Tarde", "13:00 - 17:00"],
            ["Sábado", "Mañana", "08:00 - 13:00"],
            ["Sábado", "Tarde", "13:00 - 18:00"],
        ];
    }

    /**
     * Verifica si una fecha es festiva
     * @param string $fecha Formato 'Y-m-d'
     * @return bool
     */
    public function esFestivo($fecha) {
        $conn = $this->db->connect();
        try {
            $stmt = $conn->prepare("SELECT id_festivo FROM dias_festivos WHERE fecha = :fecha");
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar festivo: " . $e->getMessage());
            return false;
        }
    }
}

class Validacion {
    /**
     * Valida los datos del formulario de programación de cursos
     * @param array $data Datos del formulario (curso, horario, fecha_inicio, duracion)
     * @return array Lista de errores, vacía si no hay
     */
    public static function validarProgramacion($data) {
        $errores = [];
        
        if (empty($data['curso'])) {
            $errores[] = "Debe seleccionar un curso";
        }
        
        if (empty($data['horario'])) {
            $errores[] = "Debe seleccionar un horario";
        }
        
        if (empty($data['fecha_inicio'])) {
            $errores[] = "Debe especificar una fecha de inicio";
        } elseif (strtotime($data['fecha_inicio']) < strtotime(date('Y-m-d'))) {
            $errores[] = "La fecha de inicio no puede ser en el pasado";
        }
        
        if (empty($data['duracion']) || !is_numeric($data['duracion']) || $data['duracion'] < 1) {
            $errores[] = "La duración debe ser un número mayor o igual a 1";
        }
        
        return $errores;
    }
}
