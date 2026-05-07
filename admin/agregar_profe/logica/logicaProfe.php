<?php
require_once '../../../BBDD/BBDD.php';

class LogicaProfesor {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerProfesores() {
        $conn = $this->db->connect();
        
        try {
            $stmt = $conn->prepare("
                SELECT pr.id_profe, pr.status, pr.especialidad, pr.fecha_registro,
                    pr.fecha_inicio_ausencia, pr.razon_ausencia,
                    pr.detalles_ausencia, pr.fecha_registro_ausencia,
                    p.nombre, p.apellido, p.cedula, p.telefono, p.correo 
                FROM profesor pr
                JOIN persona p ON p.id_persona = pr.id_persona
                ORDER BY 
                    CASE pr.status
                        WHEN 'activo' THEN 1
                        WHEN 'ausente' THEN 2
                        WHEN 'inactivo' THEN 3
                        ELSE 4
                    END,
                    p.nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener profesores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerHistorialAusencias($id_profe = null, $fecha_inicio = null) {
        $conn = $this->db->connect();
        
        $query = "SELECT 
                    pr.id_profe, 
                    pr.fecha_inicio_ausencia, 
                    pr.razon_ausencia as razon, 
                    pr.detalles_ausencia as detalles, 
                    pr.fecha_registro_ausencia as fecha_registro,
                    p.nombre, p.apellido 
                 FROM profesor pr
                 JOIN persona p ON pr.id_persona = p.id_persona
                 WHERE pr.fecha_inicio_ausencia IS NOT NULL";
        
        $params = [];
        
        if (!empty($id_profe)) {
            $query .= " AND pr.id_profe = :id_profe";
            $params[':id_profe'] = $id_profe;
        }
        
        if (!empty($fecha_inicio)) {
            $query .= " AND pr.fecha_inicio_ausencia >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        $query .= " ORDER BY pr.fecha_inicio_ausencia DESC";
        
        try {
            $stmt = $conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de ausencias: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarProfesor($id_profe) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

            // 1. Obtener el id_persona del profesor
            $stmt = $conn->prepare("SELECT id_persona FROM profesor WHERE id_profe = ?");
            $stmt->execute([$id_profe]);
            $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profesor) {
                throw new Exception("No se encontró el profesor con ID: $id_profe");
            }
            
            $id_persona = $profesor['id_persona'];

            // 2. Eliminar registros relacionados en otras tablas primero
            $stmt = $conn->prepare("UPDATE cursos SET id_profesor = NULL WHERE id_profesor = ?");
            $stmt->execute([$id_profe]);

            // 3. Eliminar el profesor
            $stmt = $conn->prepare("DELETE FROM profesor WHERE id_profe = ?");
            $stmt->execute([$id_profe]);

            // 4. Verificar si la persona está siendo usada en otras tablas antes de eliminarla
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM usuario WHERE id_persona = ?");
            $stmt->execute([$id_persona]);
            $usuario_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM estudiante WHERE id_persona = ?");
            $stmt->execute([$id_persona]);
            $estudiante_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM secretaria WHERE id_persona = ?");
            $stmt->execute([$id_persona]);
            $secretaria_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Solo eliminar la persona si no está siendo usada en otras tablas
            if ($usuario_count == 0 && $estudiante_count == 0 && $secretaria_count == 0) {
                $stmt = $conn->prepare("DELETE FROM persona WHERE id_persona = ?");
                $stmt->execute([$id_persona]);
            }

            $conn->commit();

            return [
                'success' => true, 
                'message' => 'Profesor eliminado correctamente'
            ];

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al eliminar profesor: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error al eliminar el profesor: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            return [
                'success' => false, 
                'message' => $e->getMessage()
            ];
        }
    }

    public function reactivarProfesor($id_profe) {
        $conn = $this->db->connect();
        
        try {
            $stmt = $conn->prepare("
                UPDATE profesor 
                SET 
                    status = 'activo', 
                    fecha_inicio_ausencia = NULL,
                    razon_ausencia = NULL,
                    detalles_ausencia = NULL,
                    fecha_registro_ausencia = NULL,
                    fecha_actualizacion = NOW() 
                WHERE id_profe = ?
            ");
            $stmt->execute([$id_profe]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception("No se encontró el profesor con ID: $id_profe");
            }
            
            return [
                'success' => true, 
                'message' => 'Profesor reactivado correctamente'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al reactivar profesor: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error al reactivar el profesor: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage()
            ];
        }
    }

    public function marcarComoAusente($id_profe, $datosAusencia) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

       
            $stmt = $conn->prepare("
                UPDATE profesor 
                SET 
                    fecha_inicio_ausencia = ?,
                    razon_ausencia = ?,
                    detalles_ausencia = ?,
                    fecha_registro_ausencia = NOW(),
                    status = 'ausente',
                    fecha_actualizacion = NOW()
                WHERE id_profe = ?
            ");
            
            $stmt->execute([
                $datosAusencia['fecha_inicio'],
                $datosAusencia['razon'],
                $datosAusencia['detalles'] ?? '',
                $id_profe
            ]);

            $conn->commit();

            return [
                'success' => true, 
                'message' => 'Profesor marcado como ausente correctamente'
            ];

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al marcar como ausente: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error al marcar como ausente: ' . $e->getMessage()
            ];
        }
    }

    public function agregarProfesor($datosProfesor) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

            // Insertar en la tabla persona
            $stmt = $conn->prepare("
                INSERT INTO persona (cedula, nombre, apellido, telefono, fecha_nacimiento, correo) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $datosProfesor['cedula'],
                $datosProfesor['nombre'],
                $datosProfesor['apellido'],
                $datosProfesor['telefono'],
                $datosProfesor['fecha_nacimiento'] ?? null,
                $datosProfesor['correo']
            ]);
            
            $id_persona = $conn->lastInsertId();

            // Insertar en la tabla profesor
            $stmt = $conn->prepare("
                INSERT INTO profesor (id_persona, especialidad, status, fecha_registro) 
                VALUES (?, ?, 'activo', NOW())
            ");
            
            $stmt->execute([
                $id_persona,
                $datosProfesor['especialidad']
            ]);

            $conn->commit();

            return [
                'success' => true, 
                'message' => 'Profesor agregado correctamente'
            ];

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al agregar profesor: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error al agregar el profesor: ' . $e->getMessage()
            ];
        }
    }

    public function obtenerProfesorPorId($id_profe) {
        $conn = $this->db->connect();
        
        try {
            $stmt = $conn->prepare("
                SELECT pr.id_profe, pr.status, pr.especialidad, pr.fecha_registro,
                    pr.fecha_inicio_ausencia, pr.razon_ausencia,
                    pr.detalles_ausencia, pr.fecha_registro_ausencia,
                    p.id_persona, p.nombre, p.apellido, p.cedula, p.telefono, p.correo, p.fecha_nacimiento
                FROM profesor pr
                JOIN persona p ON p.id_persona = pr.id_persona
                WHERE pr.id_profe = ?
            ");
            
            $stmt->execute([$id_profe]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener profesor: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarProfesor($id_profe, $datosProfesor) {
        $conn = $this->db->connect();
        
        try {
            $conn->beginTransaction();

           
            $stmt = $conn->prepare("SELECT id_persona FROM profesor WHERE id_profe = ?");
            $stmt->execute([$id_profe]);
            $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profesor) {
                throw new Exception("No se encontró el profesor con ID: $id_profe");
            }
            
            $id_persona = $profesor['id_persona'];

        
            $stmt = $conn->prepare("
                UPDATE persona 
                SET cedula = ?, nombre = ?, apellido = ?, telefono = ?, correo = ?, fecha_nacimiento = ?
                WHERE id_persona = ?
            ");
            
            $stmt->execute([
                $datosProfesor['cedula'],
                $datosProfesor['nombre'],
                $datosProfesor['apellido'],
                $datosProfesor['telefono'],
                $datosProfesor['correo'],
                $datosProfesor['fecha_nacimiento'] ?? null,
                $id_persona
            ]);

            // Actualizar la tabla profesor
            $stmt = $conn->prepare("
                UPDATE profesor 
                SET especialidad = ?, fecha_actualizacion = NOW()
                WHERE id_profe = ?
            ");
            
            $stmt->execute([
                $datosProfesor['especialidad'],
                $id_profe
            ]);

            $conn->commit();

            return [
                'success' => true, 
                'message' => 'Profesor actualizado correctamente'
            ];

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al actualizar profesor: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error al actualizar el profesor: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $conn->rollBack();
            return [
                'success' => false, 
                'message' => $e->getMessage()
            ];
        }
    }
}