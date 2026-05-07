<?php
require_once '../../../BBDD/BBDD.php';

class AusenciaManager
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

   
    public function registrarAusencia(int $id_profe, array $datos): array
    {
        
        if (empty($datos['razon'])) {
            return ['success' => false, 'message' => 'Debe especificar una razón'];
        }

        try {
            $this->conn->beginTransaction();

            
            $stmt = $this->conn->prepare("SELECT id_profe, historial_ausencias FROM profesor WHERE id_profe = ?");
            $stmt->execute([$id_profe]);
            $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$profesor) {
                throw new Exception("Profesor no encontrado");
            }

           
            $razonTexto = $this->getRazonTexto($datos['razon']);
            
           
            $nuevaAusencia = $razonTexto;
            
            
            $historial = $profesor['historial_ausencias'] ?? '';
            if (!empty($historial)) {
                $historial .= "\n"; 
            }
            $historial .= $nuevaAusencia;

           
            $stmt = $this->conn->prepare("
                UPDATE profesor 
                SET 
                    fecha_inicio_ausencia = ?,
                    razon_ausencia = ?,
                    detalles_ausencia = ?,
                    historial_ausencias = ?,
                    fecha_registro_ausencia = NOW(),
                    fecha_actualizacion = NOW()
                WHERE id_profe = ?
            ");
            
            $stmt->execute([
                $datos['fecha_inicio'] ?? date('Y-m-d'),
                $datos['razon'],
                $datos['detalles'] ?? '',
                $historial,
                $id_profe
            ]);

           
            $stmt = $this->conn->prepare("
                UPDATE profesor 
                SET status = 'ausente', fecha_actualizacion = NOW() 
                WHERE id_profe = ?
            ");
            $stmt->execute([$id_profe]);

            $this->conn->commit();

            return [
                'success' => true, 
                'message' => 'Ausencia registrada correctamente',
                'id_profe' => $id_profe
            ];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error BD al registrar ausencia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al registrar la ausencia: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

   
    public function obtenerHistorialAusencias(int $id_profe): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT historial_ausencias 
                FROM profesor 
                WHERE id_profe = ?
            ");
            $stmt->execute([$id_profe]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['historial_ausencias'])) {
                // Convertir el texto del historial en un array de líneas
                $lineas = explode("\n", trim($result['historial_ausencias']));
                $historial = [];
                
                foreach ($lineas as $linea) {
                    if (!empty(trim($linea))) {
                        $historial[] = ['registro' => $linea];
                    }
                }
                
                return array_reverse($historial); // Mostrar los más recientes primero
            }
            
            return [];
            
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }

   
    public function reactivarProfesor(int $id_profe): array
    {
        try {
            $this->conn->beginTransaction();

            
            $stmt = $this->conn->prepare("
                UPDATE profesor 
                SET 
                    status = 'activo', 
                    fecha_inicio_ausencia = NULL,
                    razon_ausencia = NULL,
                    detalles_ausencia = NULL,
                    fecha_registro_ausencia = NULL,
                    fecha_actualizacion = NOW() 
                WHERE id_profe = ? AND status = 'ausente'
            ");
            $stmt->execute([$id_profe]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("No se encontró profesor ausente con ese ID");
            }

            $this->conn->commit();

            return ['success' => true, 'message' => 'Profesor reactivado correctamente'];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error BD al reactivar profesor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al reactivar al profesor'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    
    public function obtenerAusenciaActual(int $id_profe): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    fecha_inicio_ausencia,
                    razon_ausencia,
                    detalles_ausencia,
                    fecha_registro_ausencia
                FROM profesor 
                WHERE id_profe = ?
            ");
            $stmt->execute([$id_profe]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (PDOException $e) {
            error_log("Error al obtener ausencia actual: " . $e->getMessage());
            return [];
        }
    }

   
    public function getRazonTexto(string $razon): string
    {
        $razones = [
            'reposo' => 'Reposo médico',
            'jubilacion' => 'Jubilación',
            'renuncia' => 'Renuncia',
            'despido' => 'Despido',
            'otra' => 'Otra razón'
        ];
        
        return $razones[$razon] ?? 'Otra razón';
    }
}


try {
    $db = new Database();
    $conn = $db->connect();
    
    
    if (!$conn) {
        die("Error de conexión a la base de datos");
    }
    
    $ausenciaManager = new AusenciaManager($conn);

  
    $id_profe = $_GET['id'] ?? null;
    $nombre_profe = "Nombre no disponible";
    $ausencias = [];
    $historial_ausencias = [];
    $ausencia_actual = [];
    $error = null;
    $success = $_GET['success'] ?? null;

    if ($id_profe) {
        try {
          
            $stmt = $conn->prepare("
                SELECT p.nombre, p.apellido, pr.status, pr.especialidad 
                FROM profesor pr 
                JOIN persona p ON pr.id_persona = p.id_persona 
                WHERE pr.id_profe = ?
            ");
            $stmt->execute([$id_profe]);
            $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($profesor) {
                $nombre_profe = $profesor['nombre'] . ' ' . $profesor['apellido'];
                $status_profe = $profesor['status'];
                $especialidad_profe = $profesor['especialidad'];
                
           
                $ausencia_actual = $ausenciaManager->obtenerAusenciaActual($id_profe);
                
               
                $historial_ausencias = $ausenciaManager->obtenerHistorialAusencias($id_profe);
            } else {
                $error = "Profesor no encontrado";
            }
            
        } catch (PDOException $e) {
            $error = "Error al cargar datos del profesor: " . $e->getMessage();
            error_log($error);
        }
    } else {
        $error = "No se ha especificado un ID de profesor";
    }

    // Procesar el formulario si se envió
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo_ausencia'])) {
        $datos = [
            'razon' => $_POST['tipo_ausencia'],
            'detalles' => $_POST['detalles'] ?? '',
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d')
        ];
        
        $resultado = $ausenciaManager->registrarAusencia($id_profe, $datos);
        
        if ($resultado['success']) {
            header("Location: ausente_profe.php?id=$id_profe&success=" . urlencode($resultado['message']));
            exit();
        } else {
            $error = $resultado['message'];
        }
    }

    if (isset($_GET['reactivar'])) {
        $resultado = $ausenciaManager->reactivarProfesor($id_profe);
        
        if ($resultado['success']) {
            header("Location: ausente_profe.php?id=$id_profe&success=" . urlencode($resultado['message']));
            exit();
        } else {
            $error = $resultado['message'];
        }
    }

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
