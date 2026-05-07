<?php
require_once '../../../controlador/persona.php';
require_once '../../../controlador/estudiante.php';
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['nombre', 'apellido', 'cedula', 'curso'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "El campo '$field' es obligatorio."
            ]);
            exit;
        }
    }

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cedula = trim($_POST['cedula']);
    $telefono = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $curso = intval($_POST['curso']);

    try {
        $fecha_nacimiento = '2000-01-01';  // fecha por defecto
        $correo = 'ninguno';                // correo por defecto

        // Verificar si la persona ya existe
        $stmt = $conn->prepare("SELECT id_persona FROM persona WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $id_persona = $stmt->fetchColumn();

        if (!$id_persona) {
            // Persona no existe: crearla
            $persona = new Persona($cedula, $nombre, $apellido, $fecha_nacimiento, $telefono, $correo);
            $id_persona = $persona->guardar($conn);
        } else {
            // Opcional: actualizar datos personales si quieres
            $stmt = $conn->prepare("UPDATE persona SET nombre = ?, apellido = ?, telefono = ? WHERE id_persona = ?");
            $stmt->execute([$nombre, $apellido, $telefono, $id_persona]);
        }

    
        // Verificar si ya existe la relación persona-curso
        $stmt = $conn->prepare("SELECT id_estudiante, estatus FROM estudiante WHERE id_persona = ? AND id_curso = ?");
        $stmt->execute([$id_persona, $curso]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro) {
            if ($registro['estatus'] === 'inactivo') {
                // Reactivar estudiante en ese curso
                $stmt = $conn->prepare("UPDATE estudiante SET estatus = 'activo' WHERE id_estudiante = ?");
                $stmt->execute([$registro['id_estudiante']]);
            } else {
                // Ya está inscrito y activo
                echo json_encode([
                    'success' => false,
                    'message' => 'El estudiante ya está inscrito en este curso.'
                ]);
                exit;
            }
        } else {
            // Insertar nuevo registro en estudiante con estatus 'activo'
            $stmt = $conn->prepare("INSERT INTO estudiante (id_persona, id_curso, estatus, fecha_inscripcion) VALUES (?, ?, 'activo', CURDATE())");
            $stmt->execute([$id_persona, $curso]);
        }




        // Obtener nombre del curso para respuesta
        $stmt = $conn->prepare("SELECT nombre_curso FROM cursos WHERE id_cursos = ?");
        $stmt->execute([$curso]);
        $nombre_curso = $stmt->fetchColumn();

        echo json_encode([
    'success' => true,
    'data' => [
        'curso' => $nombre_curso,
        'id_curso' => $curso,  
        'cedula' => $cedula,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'telefono' => $telefono
    ]
]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
}
