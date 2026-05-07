<?php
/**
 * Controlador de recuperación de contraseña
 * Maneja el envío de enlaces de recuperación por email
 */

require_once '../../../core/BaseController.php';
require_once '../../../core/Security.php';

class PasswordRecoveryController extends BaseController {
    
    /**
     * Procesa la solicitud de recuperación de contraseña
     */
    public function requestRecovery(): void {
        try {
            $this->requirePost();
            
            $data = $this->getPostData();
            $email = $data['email'] ?? '';
            
            // Validar email
            if (empty($email) || !$this->validateEmail($email)) {
                $this->redirect('/proto/login/login/vista/index.php', [
                    'error' => 'invalid_email'
                ]);
            }
            
            // Buscar usuario por email
            $user = $this->findUserByEmail($email);
            
            if ($user) {
                // Generar token de recuperación
                $token = Security::generateToken(32);
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Guardar token en base de datos
                $this->saveRecoveryToken($user['id_usuario'], $token, $expiry);
                
                // Enviar email (simulado por ahora)
                $this->sendRecoveryEmail($email, $token);
                
                $this->log("Solicitud de recuperación para email: {$email}", 'INFO');
            }
            
            // Siempre mostrar el mismo mensaje por seguridad
            $this->redirect('/proto/login/login/vista/index.php', [
                'message' => 'recovery_sent'
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Busca un usuario por email
     */
    private function findUserByEmail(string $email): ?array {
        $sql = "SELECT u.id_usuario, u.nombre_usuario, p.correo 
                FROM usuario u 
                JOIN persona p ON u.id_persona = p.id_persona 
                WHERE p.correo = :email";
        
        return $this->db->fetchOne($sql, ['email' => $email]);
    }
    
    /**
     * Guarda el token de recuperación
     */
    private function saveRecoveryToken(int $userId, string $token, string $expiry): void {
        // Eliminar tokens anteriores
        $this->db->delete(
            "DELETE FROM password_recovery WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        // Insertar nuevo token
        $this->db->insert(
            "INSERT INTO password_recovery (user_id, token, expires_at, created_at) 
             VALUES (:user_id, :token, :expires_at, NOW())",
            [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiry
            ]
        );
    }
    
    /**
     * Simula el envío de email de recuperación
     */
    private function sendRecoveryEmail(string $email, string $token): void {
        $recoveryLink = APP_URL . "/login/recuperacion/vista/reset.php?token=" . urlencode($token);
        
        // Por ahora solo registramos en log
        // En producción aquí iría la lógica real de envío de email
        $this->log("Email de recuperación enviado a {$email} con token: {$token}", 'INFO');
        $this->log("Link de recuperación: {$recoveryLink}", 'INFO');
    }
}

// Procesar la solicitud
try {
    $controller = new PasswordRecoveryController();
    $controller->requestRecovery();
} catch (Exception $e) {
    error_log("Error en recuperación de contraseña: " . $e->getMessage());
    header("Location: /proto/login/login/vista/index.php?error=system_error");
    exit;
}
?>
