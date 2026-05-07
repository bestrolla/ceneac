<?php
require_once '../../../BBDD/BBDD.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  
    echo json_encode(['success' => false, 'message' => 'Error: Método no permitido. Use POST.']);
    exit;
}


$campos = [
    'nombre' => trim($_POST['nombre'] ?? ''),
    'apellido' => trim($_POST['apellido'] ?? ''),
    'cedula' => trim($_POST['cedula'] ?? ''),
    'telefono' => trim($_POST['telefono'] ?? ''),
    'correo' => trim($_POST['correo'] ?? ''),
    'especialidad' => trim($_POST['especialidad'] ?? '')
];


foreach ($campos as $campo => $valor) {
    if (empty($valor)) {
        echo json_encode(['success' => false, 'message' => "El campo " . ucfirst($campo) . " es obligatorio"]);
        exit;
    }
}


if (!is_numeric($campos['cedula']) || $campos['cedula'] <= 100000) {
    echo json_encode([
        'success' => false, 
        'message' => 'La cédula debe ser un número mayor a 100000'
    ]);
    exit;
}


if (!filter_var($campos['correo'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false, 
        'message' => 'El correo electrónico no es válido'
    ]);
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    $conn->beginTransaction();

   
    $stmt = $conn->prepare("
        SELECT id_persona FROM persona 
        WHERE cedula = ? 
        OR correo = ? 
        OR ( ? AND apellido = ? AND telefono = ?)
    ");
    $stmt->execute([
        $campos['cedula'],
        $campos['correo'],
        $campos['nombre'],
        $campos['apellido'],
        $campos['telefono']
    ]);
    $personaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($personaExistente) {
        $id_persona = $personaExistente['id_persona'];

       
        $stmt = $conn->prepare("SELECT id_profe, status FROM profesor WHERE id_persona = ?");
        $stmt->execute([$id_persona]);
        $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profesor) {
            if ($profesor['status'] === 'inactivo') {
                
                $stmt = $conn->prepare("UPDATE profesor SET status = 'activo', especialidad = ? WHERE id_profe = ?");
                $stmt->execute([$campos['especialidad'], $profesor['id_profe']]);
                
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Profesor reactivado exitosamente'
                ]);
                exit;
            }
            
            $conn->rollBack();
            echo json_encode([
                'success' => false, 
                'message' => 'Error: Ya existe un profesor activo con estos datos'
            ]);
            exit;
        }

        // Insertar nuevo profesor (persona existente)
        $stmt = $conn->prepare("INSERT INTO profesor (id_persona, especialidad, status) VALUES (?, ?, 'activo')");
        $stmt->execute([$id_persona, $campos['especialidad']]);
        
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Profesor registrado exitosamente (persona existente)'
        ]);
        exit;
    }

    // Insertar nueva persona (si no existe)
    try {
        $stmt = $conn->prepare(
            "INSERT INTO persona (nombre, apellido, cedula, telefono, correo) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $campos['nombre'],
            $campos['apellido'],
            $campos['cedula'],
            $campos['telefono'],
            $campos['correo']
        ]);
        $id_persona = $conn->lastInsertId();
    } catch (PDOException $e) {
        $conn->rollBack();
        if ($e->getCode() == 23000) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: Los datos proporcionados ya existen en el sistema (cédula, correo o combinación apellido y teléfono)'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Insertar nuevo profesor
    $stmt = $conn->prepare(
        "INSERT INTO profesor (id_persona, especialidad, status) 
         VALUES (?, ?, 'activo')"
    );
    $stmt->execute([$id_persona, $campos['especialidad']]);

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Nuevo profesor registrado exitosamente'
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    
    
    $message = 'Error en la base de datos';
    if ($e->getCode() == 23000) {
        $message = 'Error: Datos duplicados. La cédula o correo ya existen';
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $e->getCode()
    ]);
}