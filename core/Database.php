<?php
/**
 * Clase Database mejorada para CENEAC
 * Maneja todas las conexiones y operaciones de base de datos
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instance = null;
    private $connection = null;
    private $transactionLevel = 0;
    
    private function __construct() {
        // Constructor privado para Singleton
    }
    
    /**
     * Obtiene la instancia única de Database (Singleton)
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establece la conexión a la base de datos
     */
    public function connect(): PDO {
        if ($this->connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci"
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                $this->logError("Error de conexión a la base de datos: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos");
            }
        }
        
        return $this->connection;
    }
    
    /**
     * Ejecuta una consulta preparada
     */
    public function executeQuery(string $sql, array $params = []): PDOStatement {
        $conn = $this->connect();
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Error en consulta SQL: " . $e->getMessage() . " [SQL: $sql]");
            throw new Exception("Error en la consulta de base de datos");
        }
    }
    
    /**
     * Ejecuta una consulta y retorna todos los resultados
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Ejecuta una consulta y retorna un solo resultado
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Ejecuta una consulta de inserción y retorna el ID
     */
    public function insert(string $sql, array $params = []): int {
        $conn = $this->connect();
        $stmt = $this->executeQuery($sql, $params);
        return (int) $conn->lastInsertId();
    }
    
    /**
     * Ejecuta una consulta de actualización y retorna el número de filas afectadas
     */
    public function update(string $sql, array $params = []): int {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Ejecuta una consulta de eliminación y retorna el número de filas afectadas
     */
    public function delete(string $sql, array $params = []): int {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction(): bool {
        $conn = $this->connect();
        
        if ($this->transactionLevel === 0) {
            $result = $conn->beginTransaction();
            if ($result === false) {
                throw new Exception("No se pudo iniciar la transacción");
            }
        }
        
        $this->transactionLevel++;
        return true;
    }
    
    /**
     * Confirma una transacción
     */
    public function commit(): bool {
        if ($this->transactionLevel === 0) {
            throw new Exception("No hay transacción activa para confirmar");
        }
        
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->connection->commit();
        }
        
        return true;
    }
    
    /**
     * Revierte una transacción
     */
    public function rollback(): bool {
        if ($this->transactionLevel === 0) {
            throw new Exception("No hay transacción activa para revertir");
        }
        
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->connection->rollback();
        }
        
        return true;
    }
    
    /**
     * Verifica si hay una transacción activa
     */
    public function inTransaction(): bool {
        return $this->transactionLevel > 0;
    }
    
    /**
     * Cierra la conexión
     */
    public function close(): void {
        $this->connection = null;
    }
    
    /**
     * Registra errores en el log
     */
    private function logError(string $message): void {
        $logFile = LOG_PATH . '/database_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
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

// Función helper para obtener la instancia de Database
function getDB(): Database {
    return Database::getInstance();
}
?>
