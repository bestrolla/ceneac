<?php
/**
 * Clase Security para CENEAC
 * Maneja encriptación, validación y seguridad
 */

require_once __DIR__ . '/../config/config.php';

class Security {
    
    /**
     * Encripta una contraseña usando el algoritmo configurado
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ALGORITHM, ['cost' => PASSWORD_COST]);
    }
    
    /**
     * Verifica si una contraseña coincide con su hash
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Convierte una cadena a ASCII separado por guiones (para compatibilidad)
     */
    public static function stringToAscii(string $string): string {
        $asciiArray = [];
        for ($i = 0; $i < strlen($string); $i++) {
            $asciiArray[] = ord($string[$i]);
        }
        return implode('-', $asciiArray);
    }
    
    /**
     * Convierte ASCII separado por guiones a cadena (para compatibilidad)
     */
    public static function asciiToString(string $asciiStr): string {
        $codes = explode('-', $asciiStr);
        $chars = array_map('chr', $codes);
        return implode('', $chars);
    }
    
    /**
     * Genera un token seguro
     */
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Genera un token CSRF
     */
    public static function generateCSRFToken(): string {
        $token = self::generateToken();
        getSession()->set('csrf_token', $token);
        return $token;
    }
    
    /**
     * Verifica un token CSRF
     */
    public static function verifyCSRFToken(string $token): bool {
        $sessionToken = getSession()->get('csrf_token');
        return $token === $sessionToken;
    }
    
    /**
     * Sanitiza una cadena de texto
     */
    public static function sanitizeString(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitiza un array de datos
     */
    public static function sanitizeArray(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
    
    /**
     * Valida un email
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida un nombre de usuario
     */
    public static function validateUsername(string $username): bool {
        $rules = VALIDATION_RULES['username'];
        $length = strlen($username);
        
        if ($length < $rules['min_length'] || $length > $rules['max_length']) {
            return false;
        }
        
        return preg_match($rules['pattern'], $username) === 1;
    }
    
    /**
     * Valida una contraseña
     */
    public static function validatePassword(string $password): array {
        $rules = VALIDATION_RULES['password'];
        $errors = [];
        
        $length = strlen($password);
        if ($length < $rules['min_length']) {
            $errors[] = "La contraseña debe tener al menos {$rules['min_length']} caracteres";
        }
        
        if ($length > $rules['max_length']) {
            $errors[] = "La contraseña no puede tener más de {$rules['max_length']} caracteres";
        }
        
        if ($rules['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }
        
        if ($rules['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }
        
        if ($rules['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }
        
        return $errors;
    }
    
    /**
     * Valida un teléfono
     */
    public static function validatePhone(string $phone): bool {
        return preg_match(VALIDATION_RULES['phone']['pattern'], $phone) === 1;
    }
    
    /**
     * Escapa caracteres especiales para SQL
     */
    public static function escapeSQL(string $input): string {
        return addslashes($input);
    }
    
    /**
     * Genera un salt aleatorio
     */
    public static function generateSalt(int $length = 16): string {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Verifica si una IP está en una lista blanca
     */
    public static function isIPAllowed(string $ip, array $allowedIPs): bool {
        return in_array($ip, $allowedIPs, true);
    }
    
    /**
     * Obtiene la IP real del cliente
     */
    public static function getClientIP(): string {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Registra un intento de acceso fallido
     */
    public static function logFailedAttempt(string $username, string $ip): void {
        $logFile = LOG_PATH . '/failed_attempts_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Failed login attempt - Username: {$username}, IP: {$ip}" . PHP_EOL;
        
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verifica si hay demasiados intentos fallidos
     */
    public static function hasTooManyFailedAttempts(string $username, int $maxAttempts = 5, int $timeWindow = 900): bool {
        $logFile = LOG_PATH . '/failed_attempts_' . date('Y-m-d') . '.log';
        
        if (!file_exists($logFile)) {
            return false;
        }
        
        $content = file_get_contents($logFile);
        $lines = explode(PHP_EOL, $content);
        $attempts = 0;
        $cutoffTime = time() - $timeWindow;
        
        foreach ($lines as $line) {
            if (strpos($line, "Username: {$username}") !== false) {
                preg_match('/\[(.*?)\]/', $line, $matches);
                if (isset($matches[1])) {
                    $attemptTime = strtotime($matches[1]);
                    if ($attemptTime > $cutoffTime) {
                        $attempts++;
                    }
                }
            }
        }
        
        return $attempts >= $maxAttempts;
    }
    
    /**
     * Limpia logs antiguos
     */
    public static function cleanOldLogs(int $daysToKeep = 30): void {
        $logPath = LOG_PATH;
        if (!is_dir($logPath)) {
            return;
        }
        
        $files = glob($logPath . '/*.log');
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}
?>
