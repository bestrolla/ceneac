<?php
require_once '../../../BBDD/BBDD.php'; 

class CursosLogica {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Obtiene todos los cursos, separados en activos e inactivos
    public function obtenerCursosSeparados() {
        $result = [
            'activos' => [],
            'inactivos' => []
        ];

        if (!$this->conn) {
            return $result; 
        }

        // Obtener activos
        $sqlActivos = "SELECT * FROM cursos WHERE status = 'activo'";
        $stmt = $this->conn->prepare($sqlActivos);
        $stmt->execute();
        $result['activos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener inactivos
        $sqlInactivos = "SELECT * FROM cursos WHERE status = 'inactivo'";
        $stmt = $this->conn->prepare($sqlInactivos);
        $stmt->execute();
        $result['inactivos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
}
