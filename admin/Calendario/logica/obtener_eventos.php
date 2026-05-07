<?php
// proto/admin/calendario_app/logica/obtener_eventos.php - API para obtener eventos del calendario

// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Volcar los parámetros GET recibidos al log de errores de PHP
error_log("DEBUG: Parámetros GET recibidos: " . print_r($_GET, true));

// Incluye la clase de conexión a la base de datos
// Ruta corregida: desde 'admin/calendario_app/logica/' subir TRES niveles (../../../)
// para llegar a 'proto/', y luego entrar en 'BBDD/BBDD.php'
require_once __DIR__ . '/../../../BBDD/BBDD.php';

header('Content-Type: application/json');

// VERIFICACIÓN DE AUTORIZACIÓN - Comentado para permitir acceso sin sesión
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     echo json_encode(['status' => 'error', 'message' => 'No autorizado para ver eventos.']);
//     exit();
// }

$db = new Database();
$conn = null;
try {
    $conn = $db->connect();
} catch (Exception $e) {
    // Captura el error de conexión y lo devuelve como JSON
    error_log("Error de conexión a la base de datos en obtener_eventos.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']);
    exit();
}

$events = [];

try {
    // Consulta base para obtener todos los eventos de la tabla `eventos`
    // Incluye JOINs para obtener nombres de profesor y salón
    $sql = "SELECT 
                e.id, 
                e.title, 
                e.start, 
                e.end, 
                e.description, 
                e.tipo_evento, 
                e.id_profesor_persona,
                p.nombre AS profesor_nombre,
                p.apellido AS profesor_apellido,
                e.id_salon,
                s.nombre_salon AS salon_nombre,
                e.allDay, 
                e.color,
                e.clases_tomadas,
                e.total_clases,
                e.is_rescheduled,
                e.original_course_id,
                e.class_number,
                e.class_dates_json
            FROM 
                eventos e
            LEFT JOIN 
                persona p ON e.id_profesor_persona = p.id_persona
            LEFT JOIN 
                salon s ON e.id_salon = s.id_salon";
    
    $params = [];
    $where_clause = "";

    // Lógica para filtrar por una fecha específica (usado por el panel lateral cuando se hace clic en un día)
    // Este filtro tiene prioridad si está presente y no está vacío
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        // Para filtrar por una fecha específica (para el panel lateral)
        // Un evento se superpone con el día si su inicio es antes del fin del día
        // Y su fin es después del inicio del día.
        $day_start_dt = new DateTime($_GET['date'] . ' 00:00:00');
        $day_end_dt = new DateTime($_GET['date'] . ' 23:59:59');

        $where_clause = " WHERE (e.start < :day_end AND e.end > :day_start)";
        $params[':day_start'] = $day_start_dt->format('Y-m-d H:i:s');
        $params[':day_end'] = $day_end_dt->format('Y-m-d H:i:s');
        error_log("DEBUG: Filtrando por fecha específica. Rango: " . $day_start_dt->format('Y-m-d H:i:s') . " a " . $day_end_dt->format('Y-m-d H:i:s'));
    } 
    // Lógica para filtrar por rango de fechas (usado por FullCalendar para la vista general)
    // Se usa si la fecha específica no está presente o está vacía
    else if (isset($_GET['start']) && !empty($_GET['start']) && isset($_GET['end']) && !empty($_GET['end'])) {
        // Lógica de superposición de rangos: (start1 < end2 AND end1 > start2)
        $where_clause = " WHERE (e.start < :end_date_range AND e.end > :start_date_range)";
        $params[':start_date_range'] = $_GET['start'];
        $params[':end_date_range'] = $_GET['end'];
        error_log("DEBUG: Filtrando por rango de fechas. Inicio: " . $_GET['start'] . ", Fin: " . $_GET['end']);
    } else {
        error_log("DEBUG: No se aplicaron filtros de fecha. Obteniendo todos los eventos.");
    }

    $sql .= $where_clause;
    $sql .= " ORDER BY e.start ASC";

    error_log("DEBUG: Consulta SQL final: " . $sql);
    error_log("DEBUG: Parámetros para vincular: " . print_r($params, true));

    $stmt = $conn->prepare($sql);
    
    // Pasar el array de parámetros directamente a execute()
    // Con nombres de parámetros únicos, PDO los vinculará correctamente.
    $stmt->execute($params); 
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        $allDay = (bool)$row['allDay']; // Usar el campo 'allDay' de la BD directamente

        // Si el tipo de evento es 'feriado', forzar allDay a true
        if ($row['tipo_evento'] === 'feriado') {
            $allDay = true;
        }

        $events[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'start' => $row['start'],
            'end' => $row['end'] ? $row['end'] : null, // FullCalendar prefiere null si no hay fecha de fin
            'allDay' => $allDay,
            'extendedProps' => [ // Propiedades adicionales que FullCalendar puede usar
                'description' => htmlspecialchars($row['description']),
                'tipo_evento' => htmlspecialchars($row['tipo_evento']), // Cambiado de 'tipo' a 'tipo_evento' para consistencia
                'id_profesor_persona' => $row['id_profesor_persona'],
                'profesor_nombre' => htmlspecialchars($row['profesor_nombre'] . ' ' . $row['profesor_apellido']),
                'id_salon' => $row['id_salon'],
                'salon_nombre' => htmlspecialchars($row['salon_nombre']),
                // Campos adicionales de la tabla eventos
                'clases_tomadas' => $row['clases_tomadas'],
                'total_clases' => $row['total_clases'],
                'is_rescheduled' => (bool)$row['is_rescheduled'],
                'original_course_id' => $row['original_course_id'],
                'class_number' => $row['class_number'],
                'class_dates_json' => $row['class_dates_json'] ? json_decode($row['class_dates_json']) : null,
                // Si es feriado, añadir el tipo de feriado si existe
                'tipo_feriado' => ($row['tipo_evento'] === 'feriado') ? 'nacional' : null // Asume 'nacional' o ajusta si tienes un campo en la BD para esto
            ],
            // Color del evento basado en el tipo (opcional, si no hay color en la BD)
            'backgroundColor' => getEventColor($row['tipo_evento']),
            'borderColor' => getEventColor($row['tipo_evento']),
        ];
    }

    echo json_encode($events);

} catch (PDOException $e) {
    error_log("Error al obtener eventos (PDO): " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener eventos de la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general al obtener eventos: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error inesperado al obtener los eventos.']);
} finally {
    if ($conn) {
        $db->closeConnection(); // Cerrar la conexión
    }
}

// Función auxiliar para asignar colores a los eventos
function getEventColor($eventType) {
    switch ($eventType) {
        case 'clase':
            return '#2196F3'; // Azul
        case 'reunion':
            return '#FFC107'; // Amarillo
        case 'feriado':
            return '#DC3545'; // Rojo
        default:
            return '#607D8B'; // Gris por defecto
    }
}
