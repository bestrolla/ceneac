<?php
session_start();
require_once '../../../BBDD/BBDD.php';

// Conexión a la base de datos
$db = new Database();
$conn = $db->connect();

// Función para convertir ASCII separado por guiones a cadena de texto
function asciiToString($asciiStr) {
    $codes = explode('-', $asciiStr);
    $chars = array_map('chr', $codes);
    return implode('', $chars);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validar campos
    if (empty($usuario) || empty($contrasena) || strlen($contrasena) < 8) {
        header("Location: ../vista/login.php?error=invalid_input&user=" . urlencode($usuario));
        exit;
    }

    try {
        // Buscar usuario
        $stmt = $conn->prepare("SELECT u.id_usuario, u.nombre_usuario, u.contrasena, u.id_rol, r.nombre_rol
                                FROM usuario u
                                JOIN rol r ON u.id_rol = r.id_rol
                                WHERE u.nombre_usuario = :usuario");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();

        $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioData) {

            // ** Opción actual: contraseña almacenada en ASCII, convertir y comparar en texto plano **
            $storedPassword = asciiToString($usuarioData['contrasena']);
            if ($contrasena === $storedPassword) {
                // Éxito, guardar sesión
                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuarioData['nombre_usuario'];
                $_SESSION['id_rol'] = $usuarioData['id_rol'];
                $_SESSION['nombre_rol'] = $usuarioData['nombre_rol'];

                // Redireccionar según rol
                switch ($usuarioData['nombre_rol']) {
                    case 'estudiante':
                        header('Location: ../../estudiante/vista/estudiante_panel.php');
                        break;
                    case 'secretaria':
                        header('Location: ../../../secretaria/lobby/vista/lobby.php');
                        break;
                    case 'administrador':
                        header('Location: ../../../admin/inicio/vista/inicio.php');
                        break;
                    default:
                        session_destroy();
                        header("Location: ../vista/login.php?error=system_error");
                        break;
                }
                exit;

            } else {
                // Contraseña incorrecta
                header("Location: ../vista/login.php?error=contrasena_incorrecta&user=" . urlencode($usuario));
                exit;
            }

        
            // ** Opción futura (recomendado) usando password_hash **
            // if (password_verify($contrasena, $usuarioData['contrasena'])) {
              
            // } else {
            //     header("Location: ../vista/login.php?error=contrasena_incorrecta&user=" . urlencode($usuario));
            //     exit;
            // }
        

        } else {
            // Usuario no existe
            header("Location: ../vista/login.php?error=usuario_no_existe&user=" . urlencode($usuario));
            exit;
        }

    } catch (PDOException $e) {
        error_log("Error de conexión o consulta: " . $e->getMessage());
        header("Location: ../vista/login.php?error=system_error");
        exit;
    }

} else {
    header("Location: ../vista/login.php");
    exit;
}
