<?php
// proto/admin/calendario_app/logica/create_event.php

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluye la clase de conexión a la base de datos
require_once __DIR__ . '/../../../BBDD/BBDD.php'; 

header('Content-Type: application/json');

// VERIFICACIÓN DE AUTORIZACIÓN
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || 
    ($_SESSION['nombre_rol'] !== 'administrador' && $_SESSION['nombre_rol'] !== 'secretaria')) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado para crear eventos.']);
    exit();
}

// Verifica que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit();
}

// Obtener los datos del cuerpo de la solicitud JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar datos mínimos
if (empty($data['title']) || empty($data['start'])) {
    echo json_encode(['status' => 'error', 'message' => 'Título y fecha de inicio son obligatorios.']);
    exit();
}

// Instanciar la clase de base de datos
$db = new Database();
$conn = null; 
try {
    $conn = $db->connect(); 
} catch (Exception $e) {
    error_log("Error de conexión a la base de datos en create_event.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

try {
    // Preparar datos para la inserción y la verificación de conflictos
    $title = $data['title'];
    $start = $data['start'];
    $end = $data['end'] ?? null; 
    $description = $data['description'] ?? null;
    $tipo_evento = $data['tipo_evento'] ?? 'general';
    
    // Convertir string vacío a null y a entero para claves foráneas
    $id_profesor_persona = empty($data['id_profesor_persona']) ? null : (int)$data['id_profesor_persona'];
    $id_salon = empty($data['id_salon']) ? null : (int)$data['id_salon'];
    
    $allDay = $data['allDay'] ?? 0; 
    $color = $data['color'] ?? null;
    $clases_tomadas = $data['clases_tomadas'] ?? 0;
    $total_clases = $data['total_clases'] ?? 0;
    $is_rescheduled = $data['is_rescheduled'] ?? 0;
    $original_course_id = $data['original_course_id'] ?? null;
    $class_number = $data['class_number'] ?? null;
    $classDatesJson = isset($data['class_dates_json']) && is_array($data['class_dates_json']) ? json_encode($data['class_dates_json']) : null;

    // --- Lógica de Detección de Conflictos (existente) ---

    // Calcular el 'end' para la verificación de conflictos (asegurando una duración mínima si no hay end)
    $event_start_dt = new DateTime($start); // Necesario para calcular end_for_conflict_check
    $event_end_dt = $end ? new DateTime($end) : null; // Necesario para calcular end_for_conflict_check

    $end_for_conflict_check = null;
    if ($allDay == 1) {
        if ($event_end_dt === null) {
            $end_for_conflict_check = (clone $event_start_dt)->modify('+1 day')->format('Y-m-d H:i:s');
        } else {
            $end_for_conflict_check = $event_end_dt->format('Y-m-d H:i:s');
        }
    } else {
        if ($event_end_dt === null || $event_start_dt == $event_end_dt) {
            $end_for_conflict_check = (clone $event_start_dt)->modify('+1 minute')->format('Y-m-d H:i:s');
        } else {
            $end_for_conflict_check = $event_end_dt->format('Y-m-d H:i:s');
        }
    }

    // 1. Verificar conflicto de Salón
    if ($id_salon !== null) {
        $stmt_salon_conflict = $conn->prepare("SELECT COUNT(*) FROM eventos 
                                            WHERE id_salon = :id_salon 
                                            AND (
                                                (:start_param < end AND :end_param > start)
                                            )");
        $stmt_salon_conflict->bindValue(':id_salon', $id_salon, PDO::PARAM_INT);
        $stmt_salon_conflict->bindValue(':start_param', $start);
        $stmt_salon_conflict->bindValue(':end_param', $end_for_conflict_check);
        $stmt_salon_conflict->execute();
        $conflict_count_salon = $stmt_salon_conflict->fetchColumn();

        if ($conflict_count_salon > 0) {
            echo json_encode(['status' => 'error', 'message' => 'El salón seleccionado ya está ocupado en ese horario.']);
            exit();
        }
    }

    // 2. Verificar conflicto de Profesor (si aplica y si hay un profesor asignado)
    if ($id_profesor_persona !== null) {
        $stmt_profesor_conflict = $conn->prepare("SELECT COUNT(*) FROM eventos 
                                                WHERE id_profesor_persona = :id_profesor_persona 
                                                AND (
                                                    (:start_param < end AND :end_param > start)
                                                )");
        $stmt_profesor_conflict->bindValue(':id_profesor_persona', $id_profesor_persona, PDO::PARAM_INT);
        $stmt_profesor_conflict->bindValue(':start_param', $start);
        $stmt_profesor_conflict->bindValue(':end_param', $end_for_conflict_check);
        $stmt_profesor_conflict->execute();
        $conflict_count_profesor = $stmt_profesor_conflict->fetchColumn();

        if ($conflict_count_profesor > 0) {
            echo json_encode(['status' => 'error', 'message' => 'El profesor seleccionado ya está ocupado en ese horario.']);
            exit();
        }
    }

    // --- Si no hay conflictos ni violaciones de reglas, proceder con la inserción ---
    $stmt = $conn->prepare("INSERT INTO eventos (title, start, end, description, tipo_evento, id_profesor_persona, id_salon, allDay, color, clases_tomadas, total_clases, is_rescheduled, original_course_id, class_number, class_dates_json) 
                            VALUES (:title, :start, :end, :description, :tipo_evento, :id_profesor_persona, :id_salon, :allDay, :color, :clases_tomadas, :total_clases, :is_rescheduled, :original_course_id, :class_number, :class_dates_json)");

    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end', $end); // Usar el 'end' original para guardar en la DB
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':tipo_evento', $tipo_evento);
    $stmt->bindValue(':id_profesor_persona', $id_profesor_persona, PDO::PARAM_INT);
    $stmt->bindValue(':id_salon', $id_salon, PDO::PARAM_INT);
    $stmt->bindValue(':allDay', $allDay, PDO::PARAM_INT); 
    $stmt->bindValue(':color', $color);
    $stmt->bindValue(':clases_tomadas', $clases_tomadas, PDO::PARAM_INT);
    $stmt->bindValue(':total_clases', $total_clases, PDO::PARAM_INT);
    $stmt->bindValue(':is_rescheduled', $is_rescheduled, PDO::PARAM_INT);
    $stmt->bindValue(':original_course_id', $original_course_id, PDO::PARAM_INT);
    $stmt->bindValue(':class_number', $class_number, PDO::PARAM_INT);
    $stmt->bindValue(':class_dates_json', $classDatesJson);

    $stmt->execute();

    $lastInsertId = $conn->lastInsertId();

    echo json_encode(['status' => 'success', 'message' => 'Evento creado exitosamente.', 'id' => $lastInsertId]);

} catch (PDOException $e) {
    error_log("Error al crear evento (PDO): " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al crear el evento: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general al crear evento: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado al crear el evento.']);
} finally {
    if ($conn) {
        $db->closeConnection(); // Cerrar la conexión
    }
}
