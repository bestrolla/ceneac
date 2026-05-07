<?php
/**
 * Clase Session centralizada para CENEAC
 * Maneja todas las operaciones de sesión de manera segura
 */

require_once __DIR__ . '/../config/config.php';

class Session {
    private static $instance = null;
    private $started = false;
    
    private function __construct() {
        // Constructor privado para Singleton
    }
    
    /**
     * Obtiene la instancia única de Session (Singleton)
     */
    public static function getInstance(): Session {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicia la sesión de manera segura
     */
    public function start(): bool {
        if ($this->started) {
            return true;
        }
        
        // Configurar parámetros de sesión seguros
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Configurar nombre y tiempo de vida de la sesión
        session_name(SESSION_NAME);
        session_set_cookie_params(SESSION_LIFETIME, SESSION_PATH);
        
        if (session_start()) {
            $this->started = true;
            
            // Regenerar ID de sesión periódicamente para prevenir session fixation
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica si la sesión está activa
     */
    public function isActive(): bool {
        return $this->started && session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * Establece un valor en la sesión
     */
    public function set(string $key, $value): void {
        if (!$this->isActive()) {
            $this->start();
        }
        $_SESSION[$key] = $value;
    }
    
    /**
     * Obtiene un valor de la sesión
     */
    public function get(string $key, $default = null) {
        if (!$this->isActive()) {
            $this->start();
        }
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Verifica si existe una clave en la sesión
     */
    public function has(string $key): bool {
        if (!$this->isActive()) {
            $this->start();
        }
        return isset($_SESSION[$key]);
    }
    
    /**
     * Elimina una clave de la sesión
     */
    public function remove(string $key): void {
        if (!$this->isActive()) {
            $this->start();
        }
        unset($_SESSION[$key]);
    }
    
    /**
     * Obtiene todos los datos de la sesión
     */
    public function all(): array {
        if (!$this->isActive()) {
            $this->start();
        }
        return $_SESSION;
    }
    
    /**
     * Limpia todos los datos de la sesión
     */
    public function clear(): void {
        if (!$this->isActive()) {
            $this->start();
        }
        $_SESSION = [];
    }
    
    /**
     * Destruye la sesión
     */
    public function destroy(): bool {
        if ($this->isActive()) {
            $this->clear();
            
            // Destruir la cookie de sesión
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            
            return session_destroy();
        }
        
        return true;
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    public function isAuthenticated(): bool {
        return $this->has('id_usuario') && $this->has('nombre_rol');
    }
    
    /**
     * Obtiene el ID del usuario autenticado
     */
    public function getUserId(): ?int {
        return $this->get('id_usuario');
    }
    
    /**
     * Obtiene el rol del usuario autenticado
     */
    public function getUserRole(): ?string {
        return $this->get('nombre_rol');
    }
    
    /**
     * Obtiene el nombre del usuario autenticado
     */
    public function getUserName(): ?string {
        return $this->get('nombre_usuario');
    }
    
    /**
     * Establece los datos de autenticación del usuario
     */
    public function setAuth(int $userId, string $username, string $role): void {
        $this->set('id_usuario', $userId);
        $this->set('nombre_usuario', $username);
        $this->set('nombre_rol', $role);
        $this->set('login_time', time());
    }
    
    /**
     * Limpia los datos de autenticación
     */
    public function clearAuth(): void {
        $this->remove('id_usuario');
        $this->remove('nombre_usuario');
        $this->remove('nombre_rol');
        $this->remove('login_time');
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole(string $role): bool {
        return $this->getUserRole() === $role;
    }
    
    /**
     * Verifica si el usuario tiene uno de los roles especificados
     */
    public function hasAnyRole(array $roles): bool {
        $userRole = $this->getUserRole();
        return in_array($userRole, $roles, true);
    }
    
    /**
     * Establece un mensaje flash (temporal)
     */
    public function setFlash(string $key, string $message): void {
        $this->set("flash_{$key}", $message);
    }
    
    /**
     * Obtiene y elimina un mensaje flash
     */
    public function getFlash(string $key): ?string {
        $flashKey = "flash_{$key}";
        $message = $this->get($flashKey);
        $this->remove($flashKey);
        return $message;
    }
    
    /**
     * Verifica si existe un mensaje flash
     */
    public function hasFlash(string $key): bool {
        return $this->has("flash_{$key}");
    }
    
    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
}

// Función helper para obtener la instancia de Session
function getSession(): Session {
    return Session::getInstance();
}

// Función helper para verificar acceso
function requireAuth(string $role = null): void {
    $session = getSession();
    
    if (!$session->isAuthenticated()) {
        header("Location: " . APP_URL . ROUTES['login'] . "?error=session_expired");
        exit;
    }
    
    if ($role !== null && !$session->hasRole($role)) {
        header("Location: " . APP_URL . ROUTES['login'] . "?error=access_denied");
        exit;
    }
}
?>
