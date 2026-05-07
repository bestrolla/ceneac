<?php
/**
 * Clase BaseController para CENEAC
 * Proporciona funcionalidades comunes para todos los controladores
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Security.php';

abstract class BaseController {
    protected $db;
    protected $session;
    protected $security;
    
    public function __construct() {
        $this->db = getDB();
        $this->session = getSession();
        $this->security = new Security();
        
        // Iniciar sesión automáticamente
        $this->session->start();
    }
    
    /**
     * Renderiza una vista
     */
    protected function render(string $view, array $data = []): void {
        // Extraer variables para la vista
        extract($data);
        
        // Incluir la vista
        $viewPath = APP_PATH . "/views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("Vista no encontrada: {$view}");
        }
    }
    
    /**
     * Retorna una respuesta JSON
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Retorna una respuesta de éxito
     */
    protected function successResponse(string $message, array $data = []): void {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Retorna una respuesta de error
     */
    protected function errorResponse(string $message, int $statusCode = 400): void {
        $this->jsonResponse([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
    
    /**
     * Redirige a otra página
     */
    protected function redirect(string $url, array $params = []): void {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Obtiene datos POST sanitizados
     */
    protected function getPostData(): array {
        return Security::sanitizeArray($_POST);
    }
    
    /**
     * Obtiene datos GET sanitizados
     */
    protected function getGetData(): array {
        return Security::sanitizeArray($_GET);
    }
    
    /**
     * Valida que la solicitud sea POST
     */
    protected function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Método no permitido', 405);
        }
    }
    
    /**
     * Valida que la solicitud sea GET
     */
    protected function requireGet(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->errorResponse('Método no permitido', 405);
        }
    }
    
    /**
     * Valida que el usuario esté autenticado
     */
    protected function requireAuth(string $role = null): void {
        if (!$this->session->isAuthenticated()) {
            $this->redirect(ROUTES['login'], ['error' => 'session_expired']);
        }
        
        if ($role !== null && !$this->session->hasRole($role)) {
            $this->redirect(ROUTES['login'], ['error' => 'access_denied']);
        }
    }
    
    /**
     * Valida datos requeridos
     */
    protected function validateRequired(array $data, array $required): array {
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = "El campo '{$field}' es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Ejecuta una transacción de base de datos
     */
    protected function executeTransaction(callable $callback) {
        try {
            $this->db->beginTransaction();
            $result = $callback();
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Registra un log
     */
    protected function log(string $message, string $level = 'INFO'): void {
        $logFile = LOG_PATH . '/app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtiene un mensaje de la configuración
     */
    protected function getMessage(string $type, string $key): string {
        return MESSAGES[$type][$key] ?? "Mensaje no encontrado: {$type}.{$key}";
    }
    
    /**
     * Establece un mensaje flash
     */
    protected function setFlashMessage(string $key, string $message): void {
        $this->session->setFlash($key, $message);
    }
    
    /**
     * Obtiene un mensaje flash
     */
    protected function getFlashMessage(string $key): ?string {
        return $this->session->getFlash($key);
    }
    
    /**
     * Verifica si existe un mensaje flash
     */
    protected function hasFlashMessage(string $key): bool {
        return $this->session->hasFlash($key);
    }
    
    /**
     * Genera un token CSRF
     */
    protected function generateCSRFToken(): string {
        return Security::generateCSRFToken();
    }
    
    /**
     * Verifica un token CSRF
     */
    protected function verifyCSRFToken(string $token): bool {
        return Security::verifyCSRFToken($token);
    }
    
    /**
     * Obtiene la IP del cliente
     */
    protected function getClientIP(): string {
        return Security::getClientIP();
    }
    
    /**
     * Valida un email
     */
    protected function validateEmail(string $email): bool {
        return Security::validateEmail($email);
    }
    
    /**
     * Valida un nombre de usuario
     */
    protected function validateUsername(string $username): bool {
        return Security::validateUsername($username);
    }
    
    /**
     * Valida una contraseña
     */
    protected function validatePassword(string $password): array {
        return Security::validatePassword($password);
    }
    
    /**
     * Valida un teléfono
     */
    protected function validatePhone(string $phone): bool {
        return Security::validatePhone($phone);
    }
    
    /**
     * Encripta una contraseña
     */
    protected function hashPassword(string $password): string {
        return Security::hashPassword($password);
    }
    
    /**
     * Verifica una contraseña
     */
    protected function verifyPassword(string $password, string $hash): bool {
        return Security::verifyPassword($password, $hash);
    }
    
    /**
     * Maneja errores de manera consistente
     */
    protected function handleError(Exception $e, bool $isAjax = false): void {
        $this->log("Error: " . $e->getMessage(), 'ERROR');
        
        if ($isAjax) {
            $this->errorResponse($e->getMessage());
        } else {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? ROUTES['login']);
        }
    }
    
    /**
     * Verifica si la solicitud es AJAX
     */
    protected function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Obtiene el método HTTP
     */
    protected function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Obtiene la URL actual
     */
    protected function getCurrentUrl(): string {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Obtiene el referer
     */
    protected function getReferer(): ?string {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
}
?>
