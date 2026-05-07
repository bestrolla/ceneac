<?php
require_once '../../../BBDD/BBDD.php';

class LogicaCalendario {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Obtiene las clases programadas con fechas concretas, curso y horario
     * Retorna un array con:
     *  - fecha (Y-m-d)
     *  - curso (nombre + nivel)
     *  - horario (ejemplo "09:00 - 13:00")
     */
    public function obtenerClasesProgramadas() {
        $sql = "SELECT c.id_calendario, c.dias, c.horario, c.fecha_inicio, c.fecha_fin, cu.nombre_curso, cu.nivel_cursos
                FROM calendario c
                INNER JOIN cursos cu ON c.id_cursos = cu.id_cursos
                WHERE c.fecha_fin >= CURDATE()
                ORDER BY c.fecha_inicio ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $clases = [];

        foreach ($resultados as $programacion) {
            $diasSemana = $this->parsearDiasSemana($programacion['dias']); // ej. ['Lunes', 'Miércoles', 'Viernes']
            $fechaInicio = new DateTime($programacion['fecha_inicio']);
            $fechaFin = new DateTime($programacion['fecha_fin']);
            $interval = new DateInterval('P1D');
            $periodo = new DatePeriod($fechaInicio, $interval, $fechaFin->modify('+1 day'));

            foreach ($periodo as $fecha) {
                $nombreDia = $fecha->format('l');
                $nombreDiaEs = $this->traducirDia($nombreDia);
                if (in_array($nombreDiaEs, $diasSemana)) {
                    $clases[] = [
                        'id_calendario' => $programacion['id_calendario'],
                        'fecha' => $fecha->format('Y-m-d'),
                        'curso' => $programacion['nombre_curso'] . ' - Nivel ' . $programacion['nivel_cursos'],
                        'horario' => $programacion['horario'],
                    ];
                }
            }
        }

        return $clases;
    }

    /**
     * Inserta una nueva programación de curso
     * $datos es un array con: id_cursos, dias (string), horario, fecha_inicio, fecha_fin
     */
    public function agregarProgramacion(array $datos): bool {
        $sql = "INSERT INTO calendario (id_cursos, dias, horario, fecha_inicio, fecha_fin)
                VALUES (:id_cursos, :dias, :horario, :fecha_inicio, :fecha_fin)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_cursos' => $datos['id_cursos'],
            ':dias' => $datos['dias'],
            ':horario' => $datos['horario'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin' => $datos['fecha_fin'],
        ]);
    }

    /**
     * Elimina una programación de calendario por id_calendario
     */
    public function eliminarProgramacion(int $id_calendario): bool {
        $sql = "DELETE FROM calendario WHERE id_calendario = :id_calendario";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id_calendario' => $id_calendario]);
    }

    private function parsearDiasSemana(string $dias): array {
        $diasArray = array_map('trim', explode(',', $dias));
        return $diasArray;
    }

    private function traducirDia(string $diaIngles): string {
        $mapa = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo',
        ];
        return $mapa[$diaIngles] ?? $diaIngles;
    }
}
