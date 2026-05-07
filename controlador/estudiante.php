<?php
require_once 'Persona.php';

class Estudiante {
    private $persona;
    private $id_curso;

    public function __construct(Persona $persona, $id_curso) {
        $this->persona = $persona;
        $this->id_curso = $id_curso;
    }

    public function guardar($pdo) {
        try {
            // Guardar persona primero y obtener id_persona
            $id_persona = $this->persona->guardar($pdo);
            if (!$id_persona) {
                throw new Exception("No se pudo guardar la persona");
            }

            // Insertar estudiante con id_persona y id_curso
            $stmt = $pdo->prepare("INSERT INTO estudiante (id_persona, id_curso, estatus, fecha_inscripcion) VALUES (:id_persona, :id_curso, 'activo', CURDATE())");
            $stmt->bindParam(':id_persona', $id_persona, PDO::PARAM_INT);
            $stmt->bindParam(':id_curso', $this->id_curso, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al guardar estudiante: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
