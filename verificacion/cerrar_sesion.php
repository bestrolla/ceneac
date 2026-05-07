<?php
/**
 * Controlador de logout mejorado - Utiliza el nuevo sistema de autenticación
 */

require_once '../core/AuthController.php';

try {
    // Crear instancia del controlador de autenticación
    $authController = new AuthController();
    
    // Procesar el logout usando el nuevo sistema
    $authController->logout();
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en logout: " . $e->getMessage());
    
    // Fallback: cerrar sesión manualmente y redirigir
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../login/login/vista/index.php?message=logout_success');
    exit;
}
