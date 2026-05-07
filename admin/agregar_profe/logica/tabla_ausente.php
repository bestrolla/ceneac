<?php

require_once '../logica/logicaProfe.php';
require_once '../../../verificacion/verificar_acceso.php';
verificarAcceso('administrador');

class OrdenadorAusencias {
    private $ausencias;
    private $orden;
    private $direccion;
    
    public function __construct($ausencias, $orden = 'fecha_registro_ausencia', $direccion = 'desc') {
        $this->ausencias = $ausencias;
        $this->orden = $orden;
        $this->direccion = $direccion;
    }
    
    public function ordenar() {
        if (empty($this->ausencias)) {
            return $this->ausencias;
        }
        
        usort($this->ausencias, function($a, $b) {
            $valorA = $a[$this->orden] ?? '';
            $valorB = $b[$this->orden] ?? '';
            
            if (in_array($this->orden, ['fecha_inicio_ausencia', 'fecha_registro_ausencia'])) {
                $valorA = empty($valorA) ? 0 : strtotime($valorA);
                $valorB = empty($valorB) ? 0 : strtotime($valorB);
            }
            
            if ($this->direccion === 'asc') {
                return $valorA <=> $valorB;
            } else {
                return $valorB <=> $valorA;
            }
        });
        
        return $this->ausencias;
    }
    
    public static function obtenerTextoRazon($razon) {
        $traducciones = [
            'reposo' => 'Reposo médico',
            'jubilacion' => 'Jubilación',
            'licencia' => 'Licencia',
            'capacitacion' => 'Capacitación',
            'otra' => 'Otra razón',
            'renuncia' => 'Renuncia',
            'despido' => 'Despido'
        ];
        
        return $traducciones[$razon] ?? 'Desconocido';
    }
    
    public static function obtenerDireccionOpuesta($direccionActual) {
        return $direccionActual === 'asc' ? 'desc' : 'asc';
    }
    
    public static function obtenerIconoOrdenamiento($columnaActual, $columna, $direccion) {
        if ($columnaActual !== $columna) {
            return '↕️';
        }
        return $direccion === 'asc' ? '↑' : '↓';
    }
    
    public static function generarUrlOrdenamiento($columna, $ordenActual, $direccionActual) {
        $params = $_GET;
        $params['orden'] = $columna;
        
        if ($ordenActual === $columna) {
            $params['direccion'] = self::obtenerDireccionOpuesta($direccionActual);
        } else {
            $params['direccion'] = 'asc';
        }
        
        return '?' . http_build_query($params);
    }
}


$logicaProfesor = new LogicaProfesor();
$profesores = $logicaProfesor->obtenerProfesores();


$id_profe = $_GET['id_profe'] ?? null;
$fecha_inicio = $_GET['fecha_inicio'] ?? null;

// Obtener historial de ausencias
$ausencias = $logicaProfesor->obtenerHistorialAusencias($id_profe, $fecha_inicio);


$orden = $_GET['orden'] ?? 'fecha_registro_ausencia';
$direccion = $_GET['direccion'] ?? 'desc';


$ordenador = new OrdenadorAusencias($ausencias, $orden, $direccion);
$ausenciasOrdenadas = $ordenador->ordenar();

$profesorFiltrado = null;
if (!empty($id_profe)) {
    foreach ($profesores as $prof) {
        if ($prof['id_profe'] == $id_profe) {
            $profesorFiltrado = $prof;
            break;
        }
    }
}


function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}


function esAusenciaActiva($ausencia) {
    $hoy = time();
    $inicio = strtotime($ausencia['fecha_inicio_ausencia']);
    
    
    return ($inicio <= $hoy);
}