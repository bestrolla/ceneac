<?php
require_once '../../../BBDD/BBDD.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $matricula = intval($_POST['matricula']);

  if (empty($nombre) || $matricula <= 0) {
    header("Location: ../vista/lista_salon.php?error=Datos inválidos");
    exit;
  }

  $db = new Database();
  $conn = $db->connect();

  // Verificar si el salón ya existe (por nombre)
  $stmt = $conn->prepare("SELECT * FROM salon WHERE LOWER(nombre_salon) = LOWER(?)");
  $stmt->execute([$nombre]);
  $existe = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existe) {
    // Si está inactivo, reactivarlo
    if ($existe['status'] === 'inactivo') {
      $stmt = $conn->prepare("UPDATE salon SET status='activo', matricula=? WHERE id_salon=?");
      $stmt->execute([$matricula, $existe['id_salon']]);
    }
  } else {
    $stmt = $conn->prepare("INSERT INTO salon (nombre_salon, matricula, status) VALUES (?, ?, 'activo')");
    $stmt->execute([$nombre, $matricula]);
  }

  header("Location: ../vista/lista_salon.php");
  exit;
}
