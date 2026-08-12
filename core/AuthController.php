<?php
/**
 * Controlador de Autenticación para CENEAC
 * Maneja login, logout y verificación de usuarios
 */

require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController {
    
    /**
     * Procesa el login del usuario
     */
    public function login(): void {
        try {
            $this->requirePost();
            
            $data = $this->getPostData();
            $username = $data['usuario'] ?? '';
            $password = $data['contrasena'] ?? '';
            $csrfToken = $data['csrf_token'] ?? '';
            
            // Verificar token CSRF
            if (!$this->verifyCSRFToken($csrfToken)) {
                $this->redirect('/login/login/vista/index.php', [
                    'error' => 'invalid_input',
                    'user' => $username
                ]);
            }
            
            // Validar datos requeridos
            $errors = $this->validateRequired($data, ['usuario', 'contrasena']);
            if (!empty($errors)) {
                $this->redirect('/login/login/vista/index.php', [
                    'error' => 'invalid_input',
                    'user' => $username
                ]);
            }
            
            // Verificar intentos fallidos
            if (Security::hasTooManyFailedAttempts($username)) {
                $this->redirect('/login/login/vista/index.php', [
                    'error' => 'too_many_attempts',
                    'user' => $username
                ]);
            }
            
            // Autenticar usuario
            $user = $this->authenticateUser($username, $password);
            
            if ($user) {
                // Login exitoso
                $this->session->setAuth(
                    $user['id_usuario'],
                    $user['nombre_usuario'],
                    $user['nombre_rol']
                );
                
                $this->log("Login exitoso para usuario: {$username}", 'INFO');
                
                // Redirigir según rol
                $this->redirectToRole($user['nombre_rol']);
                
            } else {
                // Login fallido
                Security::logFailedAttempt($username, $this->getClientIP());
                
                $this->redirect('/login/login/vista/index.php', [
                    'error' => 'login_failed',
                    'user' => $username
                ]);
            }
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void {
        try {
            $username = $this->session->getUserName();
            
            $this->session->clearAuth();
            $this->session->destroy();
            
            $this->log("Logout para usuario: {$username}", 'INFO');
            
            $this->redirect('/login/login/vista/index.php', ['message' => 'logout_success']);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Verifica la autenticación del usuario
     */
    public function checkAuth(): void {
        if (!$this->session->isAuthenticated()) {
            $this->redirect(ROUTES['login'], ['error' => 'session_expired']);
        }
    }
    
    /**
     * Autentica un usuario
     */
    private function authenticateUser(string $username, string $password): ?array {
        try {
            // Buscar usuario en la base de datos
            $sql = "SELECT u.id_usuario, u.nombre_usuario, u.contrasena, u.id_rol, r.nombre_rol
                    FROM usuario u
                    JOIN rol r ON u.id_rol = r.id_rol
                    WHERE u.nombre_usuario = :usuario";
            
            $user = $this->db->fetchOne($sql, ['usuario' => $username]);
            
            if (!$user) {
                return null;
            }
            
            // Verificar contraseña
            $storedPassword = $user['contrasena'];
            
            // Intentar verificar con el nuevo sistema de hash
            if (Security::verifyPassword($password, $storedPassword)) {
                return $user;
            }
            
            // Si la contraseña se guardó en texto plano, permitir login y migrar a hash
            if ($storedPassword === $password) {
                $this->migratePassword($user['id_usuario'], $password);
                return $user;
            }
            
            // Si falla, intentar con el sistema ASCII (compatibilidad)
            $asciiPassword = Security::stringToAscii($password);
            if ($asciiPassword === $storedPassword) {
                // Migrar a nuevo sistema de hash
                $this->migratePassword($user['id_usuario'], $password);
                return $user;
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->log("Error en autenticación: " . $e->getMessage(), 'ERROR');
            return null;
        }
    }
    
    /**
     * Migra una contraseña del sistema ASCII al nuevo sistema de hash
     */
    private function migratePassword(int $userId, string $password): void {
        try {
            $hashedPassword = Security::hashPassword($password);
            
            $sql = "UPDATE usuario SET contrasena = :password WHERE id_usuario = :id";
            $this->db->update($sql, [
                'password' => $hashedPassword,
                'id' => $userId
            ]);
            
            $this->log("Contraseña migrada para usuario ID: {$userId}", 'INFO');
            
        } catch (Exception $e) {
            $this->log("Error migrando contraseña: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Redirige al usuario según su rol
     */
    private function redirectToRole(string $role): void {
        switch ($role) {
            case 'administrador':
                $this->redirect('/admin/inicio/vista/inicio.php');
                break;
            case 'secretaria':
                $this->redirect('/secretaria/lobby/vista/Lobby.php');
                break;
            case 'estudiante':
                $this->redirect('/estudiante/vista/inicio.php');
                break;
            default:
                $this->redirect('/login/login/vista/index.php', ['error' => 'invalid_role']);
        }
    }
    
    /**
     * Registra un nuevo usuario
     */
    public function register(): void {
        try {
            $this->requirePost();
            
            $data = $this->getPostData();
            
            // Validar datos requeridos
            $required = ['nombre', 'apellido', 'cedula', 'fecha-nacimiento', 'telefono', 'correo', 'usuario', 'contrasena', 'confirmar-contrasena'];
            $errors = $this->validateRequired($data, $required);
            
            if (!empty($errors)) {
                $this->redirect(ROUTES['register'], [
                    'error' => 'validation_error',
                    'data' => $data
                ]);
            }
            
            // Validar contraseña
            $passwordErrors = $this->validatePassword($data['contrasena']);
            if (!empty($passwordErrors)) {
                $this->redirect(ROUTES['register'], [
                    'error' => 'password_validation',
                    'data' => $data
                ]);
            }
            
            // Verificar que las contraseñas coincidan
            if ($data['contrasena'] !== $data['confirmar-contrasena']) {
                $this->redirect(ROUTES['register'], [
                    'error' => 'password_mismatch',
                    'data' => $data
                ]);
            }
            
            // Verificar que el usuario no exista
            if ($this->userExists($data['usuario'])) {
                $this->redirect(ROUTES['register'], [
                    'error' => 'user_exists',
                    'data' => $data
                ]);
            }
            
            // Verificar que la cédula no exista
            if ($this->cedulaExists($data['cedula'])) {
                $this->redirect(ROUTES['register'], [
                    'error' => 'cedula_exists',
                    'data' => $data
                ]);
            }
            
            // Registrar usuario
            $this->executeTransaction(function() use ($data) {
                // Insertar persona
                $personaSql = "INSERT INTO persona (nombre, apellido, cedula, fecha_nacimiento, telefono, correo) 
                              VALUES (:nombre, :apellido, :cedula, :fecha_nacimiento, :telefono, :correo)";
                
                $personaId = $this->db->insert($personaSql, [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'cedula' => $data['cedula'],
                    'fecha_nacimiento' => $data['fecha-nacimiento'],
                    'telefono' => $data['telefono'],
                    'correo' => $data['correo']
                ]);
                
                // Insertar usuario
                $hashedPassword = Security::hashPassword($data['contrasena']);
                $usuarioSql = "INSERT INTO usuario (nombre_usuario, contrasena, id_rol, id_persona) 
                              VALUES (:usuario, :password, :rol, :persona_id)";
                
                $this->db->insert($usuarioSql, [
                    'usuario' => $data['usuario'],
                    'password' => $hashedPassword,
                    'rol' => ROLES['estudiante'], // Por defecto estudiante
                    'persona_id' => $personaId
                ]);
                
                return true;
            });
            
            $this->redirect(ROUTES['login'], ['message' => 'registration_success']);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Verifica si un usuario existe
     */
    private function userExists(string $username): bool {
        $sql = "SELECT COUNT(*) as count FROM usuario WHERE nombre_usuario = :usuario";
        $result = $this->db->fetchOne($sql, ['usuario' => $username]);
        return $result['count'] > 0;
    }
    
    /**
     * Verifica si una cédula existe
     */
    private function cedulaExists(string $cedula): bool {
        $sql = "SELECT COUNT(*) as count FROM persona WHERE cedula = :cedula";
        $result = $this->db->fetchOne($sql, ['cedula' => $cedula]);
        return $result['count'] > 0;
    }
}
?>
