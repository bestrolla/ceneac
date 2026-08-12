<?php
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
}

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;
    private int $transactionLevel = 0;

    public function __construct(array $config = []) {
        $this->host = $config['host'] ?? (defined('DB_HOST') ? DB_HOST : 'server036.workserverdc.com');
        $this->db_name = $config['dbname'] ?? (defined('DB_NAME') ? DB_NAME : 'bibliotecamr_ceneac');
        $this->username = $config['username'] ?? (defined('DB_USER') ? DB_USER : 'bibliotecamr_angel');
        $this->password = $config['password'] ?? (defined('DB_PASS') ? DB_PASS : 'Isis0109$');
    }

    public function connect(): PDO {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                    PDO::ATTR_PERSISTENT => false,
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

            } catch (PDOException $e) {
                // Conversión segura del código de error a entero
                $errorCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 0;
                throw new DatabaseException(
                    "Error de conexión a la base de datos: " . $e->getMessage(), 
                    $errorCode, 
                    $e
                );
            }
        }
        return $this->conn;
    }

    public function executeQuery(string $sql, array $params = []): PDOStatement {
        $this->connect();
    
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Conversión segura del código de error a entero
            $errorCode = is_numeric($e->getCode()) ? (int)$e->getCode() : 0;
            throw new DatabaseException(
                "Error en consulta SQL: " . $e->getMessage() . " [SQL: $sql]", 
                $errorCode, 
                $e
            );
        }
    }

    public function beginTransaction(): bool {
        $this->connect();
        
        if ($this->transactionLevel === 0) {
            $result = $this->conn->beginTransaction();
            if ($result === false) {
                throw new DatabaseException(
                    "No se pudo iniciar la transacción",
                    500 // Código de error genérico para errores de transacción
                );
            }
        }
        
        $this->transactionLevel++;
        return true;
    }

    public function commit(): bool {
        if ($this->transactionLevel === 0) {
            throw new DatabaseException(
                "No hay transacción activa para confirmar",
                501 // Código de error específico
            );
        }
        
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->conn->commit();
        }
        
        return true;
    }

    public function rollBack(): bool {
        if ($this->transactionLevel === 0) {
            throw new DatabaseException(
                "No hay transacción activa para revertir",
                502 // Código de error específico
            );
        }
        
        $this->transactionLevel--;
        
        if ($this->transactionLevel === 0) {
            return $this->conn->rollBack();
        }
        
        return true;
    }

    public function inTransaction(): bool {
        return $this->transactionLevel > 0;
    }

    public function closeConnection(): void {
        $this->conn = null;
    }
}

class DatabaseException extends RuntimeException {
    // Puedes añadir funcionalidad adicional específica para errores de base de datos aquí
}