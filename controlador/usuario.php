<?php
class Usuario {
    private $id_usuario;
    private $nombre_usuario;  // Cambiado de nombre_usu a nombre_usuario
    private $contrasena;
    private $id_persona;
    private $id_rol;

    public function __construct($id_usuario, $nombre_usuario, $contrasena, $id_persona, $id_rol = 1) {
        $this->id_usuario = $id_usuario;
        $this->nombre_usuario = $nombre_usuario;
        $this->contrasena = $contrasena;
        $this->id_persona = $id_persona;
        $this->id_rol = $id_rol;
    }

    public static function existeUsuario($pdo, $nombre_usuario) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE nombre_usuario = :nombre_usuario");
            $stmt->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeUsuario: " . $e->getMessage());
            throw new Exception("Error al verificar usuario existente");
        }
    }

    public function guardar($pdo) {
        try {
            // Validación básica antes de guardar
            if (empty($this->nombre_usuario)) {
                throw new Exception("El nombre de usuario no puede estar vacío");
            }
            if (empty($this->contrasena)) {
                throw new Exception("La contraseña no puede estar vacía");
            }
            if (empty($this->id_persona)) {
                throw new Exception("ID de persona no puede estar vacío");
            }

            $stmt = $pdo->prepare(
                "INSERT INTO usuario (nombre_usuario, contrasena, id_persona, id_rol) 
                 VALUES (:nombre_usuario, :contrasena, :id_persona, :id_rol)"
            );
            
            $result = $stmt->execute([
                ':nombre_usuario' => $this->nombre_usuario,
                ':contrasena' => password_hash($this->contrasena, PASSWORD_DEFAULT),
                ':id_persona' => $this->id_persona,
                ':id_rol' => $this->id_rol
            ]);
            
            if (!$result) {
                throw new Exception("Error al ejecutar la inserción");
            }
            
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error PDO al guardar usuario: " . $e->getMessage());
            throw new Exception("Error al registrar el usuario: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Error al guardar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    // Getters y Setters mejorados
    public function getIdUsuario(): ?int {
        return $this->id_usuario;
    } 
    
    public function setIdUsuario(int $id_usuario): void {
        $this->id_usuario = $id_usuario;
    }
    
    public function getNombreUsuario(): string {
        return $this->nombre_usuario;
    }
    
    public function setNombreUsuario(string $nombre_usuario): void {
        if (empty($nombre_usuario)) {
            throw new InvalidArgumentException("El nombre de usuario no puede estar vacío");
        }
        $this->nombre_usuario = $nombre_usuario;
    }
    
    public function getContrasena(): string {
        return $this->contrasena;
    }
    
    public function setContrasena(string $contrasena): void {
        if (strlen($contrasena) < 8) {
            throw new InvalidArgumentException("La contraseña debe tener al menos 8 caracteres");
        }
        $this->contrasena = $contrasena;
    }
    
    public function getIdPersona(): int {
        return $this->id_persona;
    }
    
    public function setIdPersona(int $id_persona): void {
        $this->id_persona = $id_persona;
    }
    
    public function getIdRol(): int {
        return $this->id_rol;
    }
    
    public function setIdRol(int $id_rol): void {
        $this->id_rol = $id_rol;
    }
}
?>