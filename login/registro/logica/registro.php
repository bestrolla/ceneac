<?php
require_once '../../../controlador/persona.php';
require_once '../../../controlador/usuario.php';
require_once '../../../BBDD/BBDD.php';

// Configuración de conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ceneac;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

class RegistroController {
    private $pdo;
    private $errores = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->sanitizarDatos($_POST);
            $this->validarDatos($datos);

            if (empty($this->errores)) {
                $this->registrarUsuario($datos);
                header('Location: ../../login/vista/index.php');
                exit;
            }
        }
    }

    public function obtenerErrores() {
        return $this->errores;
    }

    private function sanitizarDatos($datos) {
        return [
            'nombre' => trim($datos['nombre'] ?? ''),
            'apellido' => trim($datos['apellido'] ?? ''),
            'cedula' => trim($datos['cedula'] ?? ''),
            'fecha-nacimiento' => $datos['fecha-nacimiento'] ?? '',
            'telefono' => trim($datos['telefono'] ?? ''),
            'correo' => trim($datos['correo'] ?? ''),
            'usuario' => trim($datos['usuario'] ?? ''),
            'contrasena' => $datos['contrasena'] ?? '',
            'confirmar-contrasena' => $datos['confirmar-contrasena'] ?? ''
        ];
    }

    private function validarDatos($datos) {
        // Validar nombre
        if (empty($datos['nombre'])) {
            $this->errores[] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $datos['nombre'])) {
            $this->errores[] = 'El nombre solo debe contener letras y espacios.';
        }

        // Validar apellido
        if (empty($datos['apellido'])) {
            $this->errores[] = 'El apellido es obligatorio.';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $datos['apellido'])) {
            $this->errores[] = 'El apellido solo debe contener letras y espacios.';
        }

        // Validar cédula
        if (empty($datos['cedula'])) {
            $this->errores[] = 'La cédula es obligatoria.';
        } elseif (!preg_match('/^\d{7,8}$/', $datos['cedula'])) {
            $this->errores[] = 'La cédula debe tener entre 7 y 8 dígitos.';
        } elseif (Persona::existeCedula($this->pdo, $datos['cedula'])) {
            $this->errores[] = 'La cédula ya está registrada.';
        }

        // Validar fecha de nacimiento
        if (empty($datos['fecha-nacimiento'])) {
            $this->errores[] = 'La fecha de nacimiento es obligatoria.';
        } else {
            $fechaNac = DateTime::createFromFormat('Y-m-d', $datos['fecha-nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($fechaNac)->y;
            
            if (!$fechaNac) {
                $this->errores[] = 'La fecha de nacimiento no es válida.';
            } elseif ($edad < 18) {
                $this->errores[] = 'Debes ser mayor de 18 años para registrarte.';
            }
        }

        // Validar teléfono
        if (!empty($datos['telefono']) && !preg_match('/^[0-9]{7,15}$/', $datos['telefono'])) {
            $this->errores[] = 'El teléfono debe contener solo números (7-15 dígitos).';
        }

        // Validar correo
        if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El formato del correo electrónico no es válido.';
        }

        // Validar usuario
        if (empty($datos['usuario'])) {
            $this->errores[] = 'El nombre de usuario es obligatorio.';
        } elseif (strlen($datos['usuario']) < 6) {
            $this->errores[] = 'El usuario debe tener al menos 6 caracteres.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $datos['usuario'])) {
            $this->errores[] = 'El usuario solo puede contener letras, números y guiones bajos.';
        } elseif (Usuario::existeUsuario($this->pdo, $datos['usuario'])) {
            $this->errores[] = 'El nombre de usuario ya está en uso.';
        }

        // Validar contraseña
        if (empty($datos['contrasena'])) {
            $this->errores[] = 'La contraseña es obligatoria.';
        } elseif (strlen($datos['contrasena']) < 8) {
            $this->errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $datos['contrasena']) || 
                 !preg_match('/[a-z]/', $datos['contrasena']) || 
                 !preg_match('/[0-9]/', $datos['contrasena'])) {
            $this->errores[] = 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número.';
        } elseif ($datos['contrasena'] !== $datos['confirmar-contrasena']) {
            $this->errores[] = 'Las contraseñas no coinciden.';
        }
    }

    private function convertirAscii($cadena) {
        $ascii = array_map(function ($char) {
            return ord($char);
        }, str_split($cadena));
        return implode('-', $ascii);
    }

    private function registrarUsuario($datos) {
        try {
            $this->pdo->beginTransaction();

            // Registrar persona
            $persona = new Persona(
                null,
                $datos['cedula'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['fecha-nacimiento'],
                $datos['telefono'] ?? null,
                $datos['correo'] ?? null
            );
            
            $idPersona = $persona->guardar($this->pdo);

            // Verificar que el rol existe
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rol WHERE id_rol = :id_rol");
            $stmt->execute([':id_rol' => 1]); // 1 = estudiante
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("El rol especificado no existe");
            }

            // Convertir y cifrar la contraseña
            $asciiPassword = $this->convertirAscii($datos['contrasena']);
            $hash = password_hash($asciiPassword, PASSWORD_DEFAULT);

            // Registrar usuario
            $usuario = new Usuario(
                null,
                $datos['usuario'],
                $hash,
                $idPersona,
                1 // Rol por defecto: 1=estudiante
            );
            
            $usuario->guardar($this->pdo);
            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en registrarUsuario: " . $e->getMessage());
            throw new Exception("Error al registrar el usuario: " . $e->getMessage());
        }
    }
}

// Uso del controlador
$registroController = new RegistroController($pdo);
$registroController->procesarRegistro();
$errores = $registroController->obtenerErrores();

// Mostrar errores si existen
if (!empty($errores)) {
    echo '<div class="alert alert-danger">';
    echo '<ul>';
    foreach ($errores as $error) {
        echo "<li>$error</li>";
    }
    echo '</ul>';
    echo '</div>';
}
?>
