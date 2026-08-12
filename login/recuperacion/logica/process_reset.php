<?php
/**
 * Procesa el restablecimiento de contraseña
 */

require_once '../../../core/BaseController.php';
require_once '../../../core/Security.php';

class ProcessResetController extends BaseController {
    
    public function resetPassword(): void {
        try {
            $this->requirePost();
            
            $data = $this->getPostData();
            $token = $data['token'] ?? '';
            $password = $data['password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';
            $csrfToken = $data['csrf_token'] ?? '';
            
            // Verificar CSRF token
            if (!$this->verifyCSRFToken($csrfToken)) {
                $this->redirect("/login/recuperacion/vista/reset.php?token=" . urlencode($token), [
                    'error' => 'system_error'
                ]);
            }
            
            // Validar datos requeridos
            if (empty($token) || empty($password) || empty($confirmPassword)) {
                $this->redirect("/login/recuperacion/vista/reset.php?token=" . urlencode($token), [
                    'error' => 'system_error'
                ]);
            }
            
            // Verificar que las contraseñas coincidan
            if ($password !== $confirmPassword) {
                $this->redirect("/login/recuperacion/vista/reset.php?token=" . urlencode($token), [
                    'error' => 'password_mismatch'
                ]);
            }
            
            // Validar fortaleza de contraseña
            $passwordErrors = $this->validatePassword($password);
            if (!empty($passwordErrors)) {
                $this->redirect("/login/recuperacion/vista/reset.php?token=" . urlencode($token), [
                    'error' => 'weak_password'
                ]);
            }
            
            // Verificar token de recuperación
            $recovery = $this->verifyRecoveryToken($token);
            if (!$recovery) {
                $this->redirect("/login/recuperacion/vista/reset.php?token=" . urlencode($token), [
                    'error' => 'invalid_token'
                ]);
            }
            
            // Actualizar contraseña
            $this->executeTransaction(function() use ($recovery, $password, $token) {
                // Hash de la nueva contraseña
                $hashedPassword = $this->hashPassword($password);
                
                // Actualizar contraseña del usuario
                $this->db->update(
                    "UPDATE usuario SET contrasena = :password WHERE id_usuario = :user_id",
                    [
                        'password' => $hashedPassword,
                        'user_id' => $recovery['user_id']
                    ]
                );
                
                // Eliminar token de recuperación usado
                $this->db->delete(
                    "DELETE FROM password_recovery WHERE token = :token",
                    ['token' => $token]
                );
                
                return true;
            });
            
            $this->log("Contraseña restablecida para usuario ID: {$recovery['user_id']}", 'INFO');
            
            // Redirigir al login con mensaje de éxito
            $this->redirect('/login/login/vista/index.php', [
                'message' => 'password_reset'
            ]);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Verifica el token de recuperación
     */
    private function verifyRecoveryToken(string $token): ?array {
        $sql = "SELECT pr.*, u.nombre_usuario 
                FROM password_recovery pr 
                JOIN usuario u ON pr.user_id = u.id_usuario 
                WHERE pr.token = :token AND pr.expires_at > NOW()";
        
        return $this->db->fetchOne($sql, ['token' => $token]);
    }
}

// Procesar la solicitud
try {
    $controller = new ProcessResetController();
    $controller->resetPassword();
} catch (Exception $e) {
    error_log("Error en proceso de reset: " . $e->getMessage());
    header("Location: /login/login/vista/index.php?error=system_error");
    exit;
}
?>
