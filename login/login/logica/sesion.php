<?php
/**
 * Controlador de login mejorado - Utiliza el nuevo sistema de autenticación
 * Redirige al AuthController para mantener consistencia
 */

require_once '../../../core/AuthController.php';

try {
    // Crear instancia del controlador de autenticación
    $authController = new AuthController();
    
    // Procesar el login usando el nuevo sistema
    $authController->login();
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en login: " . $e->getMessage());
    
    // Redirigir con error
    header("Location: ../vista/index.php?error=system_error");
    exit;
}
