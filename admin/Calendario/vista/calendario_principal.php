<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

// Incluye la clase de conexión a la base de datos
require_once __DIR__ . '/../../../BBDD/BBDD.php';

// Configurar página actual y ruta base
$currentPage = 'calendario';
$basePath = '../../../'; 

// VERIFICACIÓN DE SESIÓN
// Usamos $_SESSION['loggedin'] que se configurará en el login de tu amigo
// y $_SESSION['nombre_rol'] que es la variable de rol de tu amigo
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     // Redirige al login de tu amigo si no hay sesión
//     // RUTA ABSOLUTA PARA REDIRECCIÓN
//     header("Location: /Practicas/login/login/vista/index.php"); 
//     exit();
// }

// Obtener el rol del usuario de la sesión de tu amigo
$user_role = $_SESSION['nombre_rol'] ?? 'invitado';

// Instanciar la clase de base de datos de tu amigo
$db = new Database();
$conn = null; // Inicializar $conn
try {
    $conn = $db->connect(); // Obtener la conexión PDO
} catch (Exception | PDOException $e) { // Capturar PDOException también
    echo "Error de conexión a la base de datos: " . $e->getMessage();
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - CENEAC Admin</title>

    <!-- Enlaces a los archivos CSS de FullCalendar -->
    <link href="css/fullcalendar/main.min.css" rel="stylesheet" />
    
    <!-- Tu CSS personalizado del calendario (ya incluye la integración del sidebar) -->
    <link href="css/style.css" rel="stylesheet" />
    
    <!-- Estilos del sidebar centralizado -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
</head>
<body>

<!-- Botón para abrir el sidebar - integrado con tu diseño -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

    <!-- Main Content Area Wrapper -->
    <div class="content-wrapper">
        <div class="main-content" id="main-content">
            <div id="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Panel de Detalles de Eventos -->
        <div id="event-details-panel">
            <h3>
                Eventos del Día
                <button id="close-panel-btn">&times;</button>
            </h3>
            <div id="event-list">
                <!-- Los eventos se cargarán aquí -->
                <p>Selecciona un día en el calendario para ver los eventos.</p>
            </div>
            <div id="event-summary">
                <h4>Resumen del Evento</h4>
                <p>Haz clic en un evento de la lista para ver su detalle.</p>
            </div>
            <div class="panel-actions">
                <button id="add-event-btn" class="btn-add">AGREGAR EVENTO</button>
                <button id="edit-event-btn" class="btn-edit" disabled>EDITAR EVENTO</button>
                <button id="delete-event-btn-panel" class="btn-delete" disabled>ELIMINAR EVENTO</button>
            </div>
        </div>
    </div>

    <!-- Contenedor para mensajes de notificación -->
    <div id="message-container"></div>

    <!-- Modal para Agregar/Editar Evento -->
    <div id="event-form-modal-overlay" class="event-form-modal-overlay">
        <div class="event-form-modal-content">
            <div class="modal-header">
                <h4 id="event-form-modal-title">Agregar Nuevo Evento</h4>
                <button type="button" class="close-modal-btn" id="close-event-modal-btn">&times;</button>
            </div>
            <form id="event-form">
                <input type="hidden" id="event-id"> <!-- Para guardar el ID del evento si es edición -->

                <div class="form-group">
                    <label for="event-title-input">Título:</label>
                    <input type="text" id="event-title-input" required>
                </div>

                <div class="form-group">
                    <label for="event-start-input">Fecha y Hora de Inicio:</label>
                    <input type="datetime-local" id="event-start-input" required>
                </div>

                <div class="form-group">
                    <label for="event-end-input">Fecha y Hora de Fin:</label>
                    <input type="datetime-local" id="event-end-input">
                </div>

                <div class="form-group">
                    <label for="event-description-input">Descripción:</label>
                    <textarea id="event-description-input"></textarea>
                </div>

                <div class="form-group">
                    <label for="event-type-select">Tipo de Evento:</label>
                    <select id="event-type-select">
                        <option value="clase">Clase</option>
                        <option value="reunion">Reunión</option>
                        <option value="feriado">Feriado</option>
                        <option value="general">General</option>
                    </select>
                </div>

                <!-- Campos adicionales que pueden ser necesarios para clases/reuniones -->
                <div class="form-group" id="profesor-group">
                    <label for="event-profesor-select">Profesor:</label>
                    <select id="event-profesor-select">
                        <!-- Opciones cargadas dinámicamente -->
                        <option value="">Seleccionar Profesor</option>
                    </select>
                </div>

                <div class="form-group" id="salon-group">
                    <label for="event-salon-select">Salón:</label>
                    <select id="event-salon-select">
                        <!-- Opciones cargadas dinámicamente -->
                        <option value="">Seleccionar Salón</option>
                    </select>
                </div>

                <div class="form-group" id="all-day-group">
                    <input type="checkbox" id="event-all-day-checkbox">
                    <label for="event-all-day-checkbox">Todo el día</label>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn-submit">Guardar Evento</button>
                    <button type="button" class="btn-cancel" id="cancel-event-form-btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        © 2025 CENEAC. Todos los derechos reservados.
    </footer>

    <!-- Carga de las librerías JavaScript de FullCalendar - 100% LOCAL -->
    <script src='js/fullcalendar/index.global.min.js'></script>
    <script src='js/fullcalendar/locales/es.global.min.js'></script>
    <script src='js/moment.min.js'></script>
    <script src='js/moment-timezone.min.js'></script>
    <script src='js/calendar_script.js'></script>
    
    <!-- JavaScript del sidebar centralizado -->
    <?= renderSidebarScript() ?>
    
</body>
</html>
