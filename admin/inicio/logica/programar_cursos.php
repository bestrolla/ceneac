<?php
require_once '../logica/clases_logica.php';
require_once '../../../verificacion/verificar_acceso.php';
require_once '../../../BBDD/BBDD.php';

verificarAcceso('administrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener datos del formulario
    $id_curso = $_POST['curso'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    
    // Validar datos requeridos
    if (empty($id_curso) || empty($horario) || empty($fecha_inicio)) {
        $_SESSION['mensaje'] = "Error: Todos los campos son requeridos.";
        header("Location: ../vista/inicio.php");
        exit();
    }
    
    // Separar los componentes del horario
    $partes_horario = explode('|', $horario);
    if (count($partes_horario) !== 3) {
        $_SESSION['mensaje'] = "Error: Formato de horario inválido.";
        header("Location: ../vista/inicio.php");
        exit();
    }
    
    list($dias, $turno, $rango_horario) = $partes_horario;
    
    try {
        // Obtener la duración del curso desde la base de datos
        $stmt = $conn->prepare("SELECT duracion FROM cursos WHERE id_cursos = :id_curso");
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$curso) {
            $_SESSION['mensaje'] = "Error: Curso no encontrado.";
            header("Location: ../vista/inicio.php");
            exit();
        }
        
        $duracion = $curso['duracion'];
        
        // Validar que la duración sea un número válido
        if (!is_numeric($duracion) || $duracion <= 0) {
            $_SESSION['mensaje'] = "Error: La duración del curso no es válida.";
            header("Location: ../vista/inicio.php");
            exit();
        }
        
        // Usar la lógica correcta para calcular la fecha fin
        $logica = new CursoLogica();
        $fecha_fin = $logica->programarCurso($id_curso, $dias, $rango_horario, $fecha_inicio, intval($duracion));
        
        $_SESSION['mensaje'] = "Curso programado exitosamente.";
        header("Location: ../vista/inicio.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al programar el curso: " . $e->getMessage();
        header("Location: ../vista/inicio.php");
        exit();
    }
}