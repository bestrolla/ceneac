<?php
/**
 * Inserta un curso y genera automáticamente los eventos del calendario
 * según los días seleccionados, rango de fechas y horario.
 *
 */

require_once '../../../BBDD/BBDD.php';

$isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
);

// Función de salida unificada
function respond($ok, $msg, $redirectOnHtml = '../vista/lista_cursos.php') {
    global $isAjax;
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        $q = $ok ? 'ok=1' : 'error=' . urlencode($msg);
        header("Location: {$redirectOnHtml}?{$q}");
    }
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Método no permitido.');
}

try {
    $db = new Database();
    $conn = $db->connect(); // PDO
    if (!$conn) {
        respond(false, 'No se pudo conectar a la base de datos.');
    }

    // 1) Recoger datos base del curso
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $duracion    = trim($_POST['duracion'] ?? '');
    $nivel       = trim($_POST['nivel'] ?? '');

    // 2) Datos de asignación opcionales
    $idProfesorPersona = isset($_POST['id_profesor_persona']) && $_POST['id_profesor_persona'] !== ''
        ? (int)$_POST['id_profesor_persona'] : null;

    $idSalon = isset($_POST['id_salon']) && $_POST['id_salon'] !== ''
        ? (int)$_POST['id_salon'] : null;

    // 3) Datos de calendario (obligatorios)
    $fechaInicio = $_POST['fecha_inicio'] ?? '';
    $fechaFin    = $_POST['fecha_fin'] ?? '';
    $horaInicio  = $_POST['hora_inicio'] ?? '';
    $horaFin     = $_POST['hora_fin'] ?? '';
    $dias        = $_POST['dias'] ?? []; // array de enteros 1..7 (Mon..Sun)

    // --- VALIDACIONES BÁSICAS ---
    if ($nombre === '' || $duracion === '' || $nivel === '') {
        respond(false, 'Complete nombre, duración y nivel.');
    }
    if ($fechaInicio === '' || $fechaFin === '' || $horaInicio === '' || $horaFin === '') {
        respond(false, 'Complete fechas y horarios.');
    }
    if (!is_array($dias) || count($dias) === 0) {
        respond(false, 'Seleccione al menos un día de la semana.');
    }

    // Normalizar / validar fechas y horas
    $dtInicio = DateTime::createFromFormat('Y-m-d', $fechaInicio);
    $dtFin    = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if (!$dtInicio || !$dtFin || $dtFin < $dtInicio) {
        respond(false, 'Rango de fechas inválido.');
    }
    $tInicio = DateTime::createFromFormat('H:i', $horaInicio);
    $tFin    = DateTime::createFromFormat('H:i', $horaFin);
    if (!$tInicio || !$tFin || $tFin <= $tInicio) {
        respond(false, 'Horario inválido: la hora fin debe ser mayor que la de inicio.');
    }

    // Normalizamos array de días a enteros únicos entre 1..7
    $diasSemana = array_unique(array_map('intval', $dias));
    $diasSemana = array_values(array_filter($diasSemana, function($d) { return $d >= 1 && $d <= 7; }));
    if (count($diasSemana) === 0) {
        respond(false, 'Los días seleccionados no son válidos.');
    }

    // --- TRANSACCIÓN ---
    $db->beginTransaction();

    // 4) Insertar el curso
    $sqlCurso = "INSERT INTO cursos (nombre_curso, descripcion, duracion, nivel_cursos, status)
                 VALUES (:n, :d, :du, :ni, 'activo')";
    $st = $conn->prepare($sqlCurso);
    $st->execute([
        ':n'  => $nombre,
        ':d'  => $descripcion !== '' ? $descripcion : null,
        ':du' => $duracion,
        ':ni' => $nivel
    ]);
    $idCurso = (int)$conn->lastInsertId();

    // 5) Armar todas las fechas de clase según rango + días
    //    a) Precalcular lista de fechas para numerar clases y total_clases
    $todasLasFechas = [];
    $cursor = clone $dtInicio;
    while ($cursor <= $dtFin) {
        $n = (int)$cursor->format('N'); // 1=Lunes ... 7=Domingo
        if (in_array($n, $diasSemana, true)) {
            $todasLasFechas[] = $cursor->format('Y-m-d');
        }
        $cursor->modify('+1 day');
    }

    if (count($todasLasFechas) === 0) {
        // No hay coincidencias; no generaría eventos
        throw new RuntimeException('El rango de fechas + días seleccionados no produce ninguna sesión.');
    }

    $totalClases = count($todasLasFechas);
    $classDatesJson = json_encode($todasLasFechas, JSON_UNESCAPED_UNICODE);

    // 6) Insertar eventos en `eventos` con verificación de conflictos (si hay profesor/salón)
    //    Reutilizamos la lógica de solapamiento usada en tu create_event.php:
    //    ( :start < end AND :end > start )
    $sqlConflictSalon = null;
    $sqlConflictProf  = null;

    if ($idSalon !== null) {
        $sqlConflictSalon = $conn->prepare("
            SELECT COUNT(*) FROM eventos
            WHERE id_salon = :id_salon AND (:start_dt < `end` AND :end_dt > `start`)
        ");
    }

    if ($idProfesorPersona !== null) {
        $sqlConflictProf = $conn->prepare("
            SELECT COUNT(*) FROM eventos
            WHERE id_profesor_persona = :id_prof AND (:start_dt < `end` AND :end_dt > `start`)
        ");
    }

    $sqlInsertEvento = $conn->prepare("
        INSERT INTO eventos (
            title, `start`, `end`, description, tipo_evento,
            id_profesor_persona, id_salon, allDay, color,
            clases_tomadas, total_clases, is_rescheduled,
            original_course_id, class_number, class_dates_json
        ) VALUES (
            :title, :start, :end, :descr, 'clase',
            :id_prof, :id_salon, 0, NULL,
            0, :total, 0,
            :orig_id, :class_number, :class_dates_json
        )
    ");

    $classNumber = 0;
    foreach ($todasLasFechas as $fechaClase) {
        $classNumber++;

        // Construir start/end exactos de esa fecha
        $startDT = DateTime::createFromFormat('Y-m-d H:i', $fechaClase . ' ' . $horaInicio);
        $endDT   = DateTime::createFromFormat('Y-m-d H:i', $fechaClase . ' ' . $horaFin);

        $startStr = $startDT->format('Y-m-d H:i:s');
        $endStr   = $endDT->format('Y-m-d H:i:s');

        // Conflictos (solo si hay profesor o salón informados)
        if ($sqlConflictSalon) {
            $sqlConflictSalon->execute([
                ':id_salon' => $idSalon,
                ':start_dt' => $startStr,
                ':end_dt'   => $endStr
            ]);
            if ((int)$sqlConflictSalon->fetchColumn() > 0) {
                throw new RuntimeException("Conflicto: el salón seleccionado está ocupado el $fechaClase de $horaInicio a $horaFin.");
            }
        }

        if ($sqlConflictProf) {
            $sqlConflictProf->execute([
                ':id_prof'  => $idProfesorPersona,
                ':start_dt' => $startStr,
                ':end_dt'   => $endStr
            ]);
            if ((int)$sqlConflictProf->fetchColumn() > 0) {
                throw new RuntimeException("Conflicto: el profesor seleccionado ya tiene evento el $fechaClase de $horaInicio a $horaFin.");
            }
        }

        // Insertar el evento
        $sqlInsertEvento->execute([
            ':title'            => $nombre,
            ':start'            => $startStr,
            ':end'              => $endStr,
            ':descr'            => $descripcion !== '' ? $descripcion : null,
            ':id_prof'          => $idProfesorPersona,
            ':id_salon'         => $idSalon,
            ':total'            => $totalClases,
            ':orig_id'          => $idCurso,          // vínculo curso
            ':class_number'     => $classNumber,       // numeración de clase
            ':class_dates_json' => $classDatesJson     // todas las fechas en JSON (útil para el cliente)
        ]);
    }

    // 7) Commit final
    $db->commit();
    respond(true, 'Curso y calendario creados correctamente.');

} catch (Throwable $e) {
    // Rollback y mensaje de error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    respond(false, 'No se pudo registrar: ' . $e->getMessage());
}

