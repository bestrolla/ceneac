<?php 

require_once '../logica/logicaProfe.php';
require_once '../../../verificacion/verificar_acceso.php';
verificarAcceso('administrador');


$logicaProfesor = new LogicaProfesor();
$profesores = $logicaProfesor->obtenerProfesores();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_profe'])) {
    $id_profe = $_POST['id_profe'] ?? null;
    
    if ($id_profe) {
        $resultado = $logicaProfesor->eliminarProfesor($id_profe);
        
        if ($resultado['success']) {
           
            header("Location: lista_profe.php?success=" . urlencode($resultado['message']));
            exit();
        } else {
            $error = $resultado['message'];
        }
    }
}


$success = $_GET['success'] ?? null;

?>