<?php
class Persona {
    private $id_persona;
    private $cedula;
    private $nombre;
    private $apellido;
    private $fecha_nacimiento;
    private $telefono;
    private $correo;

    public function __construct($cedula, $nombre, $apellido, $fecha_nacimiento = '2000-01-01', $telefono = null, $correo = null) {
        $this->id_persona = null; // se asigna al guardar
        $this->cedula = $cedula;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->telefono = $telefono;
        $this->correo = $correo;
    }

    public static function existeCedula($pdo, $cedula) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM persona WHERE cedula = :cedula");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    public function guardar($pdo) {
        try {
            // Validar que no exista persona con misma cédula
            if (self::existeCedula($pdo, $this->cedula)) {
                throw new Exception("Ya existe una persona con esa cédula");
            }

            $stmt = $pdo->prepare(
                "INSERT INTO persona (cedula, nombre, apellido, fecha_nacimiento, telefono, correo) 
                 VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :telefono, :correo)"
            );

            $stmt->bindParam(':cedula', $this->cedula);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellido', $this->apellido);
            $stmt->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':correo', $this->correo);

            if ($stmt->execute()) {
                $this->id_persona = $pdo->lastInsertId();
                return $this->id_persona;
            } else {
                throw new Exception("Error al guardar la persona");
            }
        } catch (PDOException $e) {
            error_log("Error al guardar persona: " . $e->getMessage());
            throw new Exception("Error al registrar los datos personales: " . $e->getMessage());
        }
    }

    // Getters y setters...
    public function getIdPersona() {
        return $this->id_persona;
    }
    public function getCedula() {
        return $this->cedula;
    }

    public function setCedula($cedula) {
        $this->cedula = $cedula;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getApellido() {
        return $this->apellido;
    }

    public function setApellido($apellido) {
        $this->apellido = $apellido;
    }

    public function getFechaNacimiento() {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento($fecha_nacimiento) {
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }
    // Otros getters y setters pueden añadirse si es necesario
}
?>
