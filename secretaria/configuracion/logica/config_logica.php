<?php
session_start();
require_once '../../../BBDD/BBDD.php';

// Función para convertir cadena a ASCII separada por guiones
function stringToAscii($string) {
    $asciiArray = [];
    for ($i = 0; $i < strlen($string); $i++) {
        $asciiArray[] = ord($string[$i]);
    }
    return implode('-', $asciiArray);
}

// Función para convertir ASCII separado por guiones a cadena
function asciiToString($asciiStr) {
    $codes = explode('-', $asciiStr);
    $chars = array_map('chr', $codes);
    return implode('', $chars);
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    $_SESSION['mensaje_config'] = "Error al conectar con la base de datos.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../vista/configuracion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    $nueva_contrasena = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar_contrasena = trim($_POST['confirmar_contrasena'] ?? '');

    if (empty($nuevo_usuario) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        $_SESSION['mensaje_config'] = "Todos los campos son obligatorios.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../vista/configuracion.php");
        exit;
    }

    if ($nueva_contrasena !== $confirmar_contrasena) {
        $_SESSION['mensaje_config'] = "Las contraseñas no coinciden.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../vista/configuracion.php");
        exit;
    }

    // Validar si el nombre de usuario ya existe (distinto al actual)
    try {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE nombre_usuario = :usuario AND id_usuario != :id");
        $stmt_check->bindParam(':usuario', $nuevo_usuario);
        $stmt_check->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);
        $stmt_check->execute();
        $existe = $stmt_check->fetchColumn();

        if ($existe > 0) {
            $_SESSION['mensaje_config'] = "El nombre de usuario ya está en uso.";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../vista/configuracion.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje_config'] = "Error al validar usuario: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../vista/configuracion.php");
        exit;
    }

    // Convertir contraseña a ASCII separada por guiones
    $clave_ascii = stringToAscii($nueva_contrasena);

    try {
        $stmt = $conn->prepare("
            UPDATE usuario 
            SET nombre_usuario = :usuario, contrasena = :clave 
            WHERE id_usuario = :id
        ");
        $stmt->bindParam(':usuario', $nuevo_usuario);
        $stmt->bindParam(':clave', $clave_ascii);
        $stmt->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['mensaje_config'] = "Datos actualizados correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje_config'] = "No se pudo actualizar la información.";
            $_SESSION['tipo_mensaje'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje_config'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "error";
    }

    header("Location: ../vista/configuracion.php");
    exit;
} else {
    $_SESSION['mensaje_config'] = "Acceso no permitido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../vista/configuracion.php");
    exit;
}
