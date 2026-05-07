<?php
require_once '../../../BBDD/BBDD.php';

class SalonLogica {
  private $conn;

  public function __construct() {
    $db = new Database();
    $this->conn = $db->connect();
  }

  public function obtenerSalonesSeparados() {
    $sql = "SELECT * FROM salon ";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $salones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $activos = [];
    $inactivos = [];

    foreach ($salones as $salon) {

      if ($salon['status'] === 'activo') {
        $activos[] = $salon;
      }else{
        if ($salon['status'] === 'inactivo') {
          $inactivos[] = $salon;
        }
      }
    }

    return ['activo' => $activos, 'inactivo' => $inactivos];
  }
}
