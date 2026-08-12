<?php
/**
 * Verificación de acceso mejorada - Utiliza el nuevo sistema de sesiones
 */

require_once __DIR__ . '/../core/Session.php';

/**
 * Verifica el acceso del usuario basado en el rol requerido
 * @param string $rolEsperado El rol requerido para acceder
 * @param bool $redirect Si debe redirigir automáticamente en caso de error
 * @return bool True si tiene acceso, False si no
 */
function verificarAcceso($rolEsperado, $redirect = true) {
    $session = getSession();
    $session->start();
    
    // Verificar si está autenticado
    if (!$session->isAuthenticated()) {
        if ($redirect) {
            header("Location: /login/login/vista/index.php?error=session_expired");
            exit;
        }
        return false;
    }
    
    // Verificar rol específico
    if (!$session->hasRole($rolEsperado)) {
        if ($redirect) {
            header("Location: /login/login/vista/index.php?error=access_denied");
            exit;
        }
        return false;
    }
    
    return true;
}

/**
 * Verifica si el usuario tiene cualquiera de los roles especificados
 * @param array $rolesPermitidos Array de roles permitidos
 * @param bool $redirect Si debe redirigir automáticamente en caso de error
 * @return bool True si tiene acceso, False si no
 */
function verificarAccesoMultiple($rolesPermitidos, $redirect = true) {
    $session = getSession();
    $session->start();
    
    // Verificar si está autenticado
    if (!$session->isAuthenticated()) {
        if ($redirect) {
            header("Location: /login/login/vista/index.php?error=session_expired");
            exit;
        }
        return false;
    }
    
    // Verificar si tiene alguno de los roles permitidos
    if (!$session->hasAnyRole($rolesPermitidos)) {
        if ($redirect) {
            header("Location: /login/login/vista/index.php?error=access_denied");
            exit;
        }
        return false;
    }
    
    return true;
}

/**
 * Función helper para requerir autenticación básica
 */
if (!function_exists('requireAuth')) {
    function requireAuth() {
        $session = getSession();
        $session->start();
        
        if (!$session->isAuthenticated()) {
            header("Location: /login/login/vista/index.php?error=session_expired");
            exit;
        }
    }
}

/**
 * Obtiene información del usuario actual
 * @return array|null Información del usuario o null si no está autenticado
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        $session = getSession();
        $session->start();
        
        if (!$session->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $session->getUserId(),
            'username' => $session->getUserName(),
            'role' => $session->getUserRole()
        ];
    }
}
?>
