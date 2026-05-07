<?php
require_once '../../../BBDD/BBDD.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db = new Database();
$conn = $db->connect();

$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$cedula   = trim($_POST['cedula'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$fecha_inicio = date('Y-m-d');

// Validaciones
if (!$nombre || !$apellido || !$cedula || !$telefono || !$correo) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if (!is_numeric($cedula) || (int)$cedula < 100000) {
    echo json_encode(['success' => false, 'message' => 'La cédula debe ser un número mayor a 100000']);
    exit;
}

// Nueva lógica para usuario y contraseña
function generarUsuario($nombre) {
    $primerNombre = explode(" ", $nombre)[0];
    return $primerNombre . "09$";
}

function generarClave($apellido, $telefono) {
    $primerApellido = explode(" ", $apellido)[0];
    // Tomar los primeros 4 dígitos numéricos del teléfono
    $telCorto = substr(preg_replace('/\D/', '', $telefono), 0, 4);
    return $primerApellido . $telCorto . "*=";
}

$usuario = generarUsuario($nombre);
$clave   = generarClave($apellido, $telefono);

try {
    $conn->beginTransaction();

    // Verificar persona existente
    $stmt = $conn->prepare("SELECT id_persona FROM persona WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($persona) {
        $id_persona = $persona['id_persona'];

        // Verificar secretaria existente
        $stmt = $conn->prepare("SELECT status FROM secretaria WHERE id_persona = ?");
        $stmt->execute([$id_persona]);
        $secretaria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($secretaria) {
            echo json_encode(['success' => false, 'message' => 'La cédula ya está registrada como secretaria']);
            $conn->rollBack();
            exit;
        }

        // Crear usuario si no existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_persona = ?");
        $stmt->execute([$id_persona]);
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuarioExistente) {
            $stmt = $conn->prepare("INSERT INTO usuario (nombre_usuario, contrasena, id_persona, id_rol) VALUES (?, ?, ?, 4)");
            $stmt->execute([$usuario, $clave, $id_persona]);
            $id_usuario = $conn->lastInsertId();
        } else {
            $id_usuario = $usuarioExistente['id_usuario'];
        }

        // Insertar secretaria nueva
        $stmt = $conn->prepare("INSERT INTO secretaria (fecha_inicio, id_persona, id_usuario, status) VALUES (?, ?, ?, 'activo')");
        $stmt->execute([$fecha_inicio, $id_persona, $id_usuario]);

    } else {
        // Insertar nueva persona
        $stmt = $conn->prepare("INSERT INTO persona (nombre, apellido, cedula, telefono, correo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $cedula, $telefono, $correo]);
        $id_persona = $conn->lastInsertId();

        // Crear usuario
        $stmt = $conn->prepare("INSERT INTO usuario (nombre_usuario, contrasena, id_persona, id_rol) VALUES (?, ?, ?, 4)");
        $stmt->execute([$usuario, $clave, $id_persona]);
        $id_usuario = $conn->lastInsertId();

        // Insertar secretaria
        $stmt = $conn->prepare("INSERT INTO secretaria (fecha_inicio, id_persona, id_usuario, status) VALUES (?, ?, ?, 'activo')");
        $stmt->execute([$fecha_inicio, $id_persona, $id_usuario]);
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Secretaria agregada correctamente',
        'usuario' => $usuario,
        'clave'   => $clave
    ]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
