<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/logica_calendario.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

$logica = new LogicaCalendario();
$clases = $logica->obtenerClasesProgramadas();

// Configurar página actual y ruta base
$currentPage = 'calendario';
$basePath = '../../../';

// Redireccionar al calendario principal si este es solo una página de índice
// header("Location: calendario_principal.php");
// exit();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Calendario Simple - CENEAC Admin</title>
  
  <!-- Estilos específicos del calendario -->
  <link rel="stylesheet" href="css/styles.css" />
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <style>
    /* Ajustes para integrar con tu CSS */
    .custom-menu-btn {
      background: #007bff;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s ease;
      position: fixed;
      top: 15px;
      left: 20px;
      z-index: 1002;
    }
    
    .custom-menu-btn:hover {
      background: #0056b3;
      transform: scale(1.05);
    }
    
    /* Ocultar barra superior centralizada */
    .top-bar {
      display: none !important;
    }
    
    /* Compatibilidad con sidebar */
    body.sidebar-open .content-wrapper {
      margin-left: 250px;
    }
    
    @media (max-width: 768px) {
      body.sidebar-open .content-wrapper {
        margin-left: 0;
      }
    }
  </style>
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon" />
  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/main.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/main.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.9/main.min.css" rel="stylesheet">
</head>
<body>

<!-- Botón para abrir el sidebar -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

<div class="content-wrapper">
  <main class="main-content">
    <div class="calendar-info" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #007bff;">
      <h1>Calendario de Clases Programadas</h1>
      <p style="margin: 10px 0; color: #6c757d;">Esta es una vista simplificada del calendario. Para acceder a todas las funcionalidades de gestión de eventos, 
      <a href="calendario_principal.php" style="color: #007bff; text-decoration: none; font-weight: 600;">haz clic aquí para ir al calendario principal</a>.</p>
    </div>
    <div id="calendario" class="calendario"></div>
  </main>
</div>

  <footer>
    <p> 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.9/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.9/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/locales/es.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const clasesProgramadas = <?php echo json_encode($clases); ?>;
      const calendarEl = document.getElementById('calendario');

      // Formatear eventos para FullCalendar
      const events = clasesProgramadas.map(clase => ({
        title: `${clase.nombre_curso}${clase.horario ? ` (${clase.horario})` : ''}`,
        start: clase.fecha_inicio,
        end: clase.fecha_fin || clase.fecha_inicio,
        allDay: false,
        extendedProps: {
          profesor: clase.nombre_profesor || 'Sin asignar',
          salon: clase.nombre_salon || 'Sin asignar'
        }
      }));

      const calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [FullCalendar.dayGridPlugin, FullCalendar.timeGridPlugin, FullCalendar.interactionPlugin],
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: events,
        eventTimeFormat: {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        },
        eventClick: function(info) {
          const event = info.event;
          alert(
            `Curso: ${event.title}\n` +
            `Profesor: ${event.extendedProps.profesor}\n` +
            `Salón: ${event.extendedProps.salon}\n` +
            `Inicio: ${event.start.toLocaleString()}\n` +
            `Fin: ${event.end ? event.end.toLocaleString() : 'Mismo día'}`
          );
        }
      });

      calendar.render();
    });
  </script>

  <script src="botones.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSidebarScript() ?>
  
</body>
</html>