<?php
/**
 * Configuración centralizada del proyecto CENEAC
 * Este archivo contiene todas las configuraciones globales
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'ceneac');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'CENEAC');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/proto');
define('APP_PATH', __DIR__ . '/..');

// Configuración de sesión
define('SESSION_NAME', 'CENEAC_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_PATH', '/');

// Configuración de seguridad
define('PASSWORD_ALGORITHM', PASSWORD_ARGON2ID);
define('PASSWORD_COST', 12);
define('TOKEN_EXPIRY', 3600); // 1 hora

// Configuración de roles
define('ROLES', [
    'administrador' => 1,
    'secretaria' => 2,
    'estudiante' => 3
]);

// Configuración de estados
define('STATUS', [
    'activo' => 'activo',
    'inactivo' => 'inactivo',
    'ausente' => 'ausente'
]);

// Configuración de rutas
define('ROUTES', [
    'login' => '/login/login/vista/index.php',
    'admin' => '/admin/inicio/vista/inicio.php',
    'secretaria' => '/secretaria/lobby/vista/Lobby.php',
    'estudiante' => '/estudiante/vista/inicio.php'
]);

// Configuración de mensajes
define('MESSAGES', [
    'success' => [
        'login' => 'Inicio de sesión exitoso',
        'logout' => 'Sesión cerrada correctamente',
        'save' => 'Datos guardados correctamente',
        'delete' => 'Elemento eliminado correctamente',
        'update' => 'Datos actualizados correctamente'
    ],
    'error' => [
        'login_failed' => 'Usuario o contraseña incorrectos',
        'access_denied' => 'Acceso denegado',
        'session_expired' => 'Sesión expirada',
        'database_error' => 'Error en la base de datos',
        'validation_error' => 'Error de validación'
    ]
]);

// Configuración de validación
define('VALIDATION_RULES', [
    'username' => [
        'min_length' => 3,
        'max_length' => 50,
        'pattern' => '/^[a-zA-Z0-9_]+$/'
    ],
    'password' => [
        'min_length' => 8,
        'max_length' => 255,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true
    ],
    'email' => [
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
    ],
    'phone' => [
        'pattern' => '/^[0-9+\-\s()]+$/'
    ]
]);

// Configuración de archivos
define('UPLOAD_PATH', APP_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Configuración de logging
define('LOG_PATH', APP_PATH . '/logs');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de timezone
date_default_timezone_set('America/Caracas');

// Configuración de errores (solo para desarrollo)
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Función para obtener configuración
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Función para validar configuración
function validateConfig() {
    $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'APP_URL'];
    foreach ($required as $config) {
        if (!defined($config) || empty(constant($config))) {
            throw new Exception("Configuración requerida no encontrada: {$config}");
        }
    }
}

// Validar configuración al cargar
validateConfig();
?>
