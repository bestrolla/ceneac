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
//     header("Location: proto/login/login/vista/index.php"); 
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

    <!-- Estilos del sidebar centralizado -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
    
    <!-- Estilos responsive centralizados -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
    
    <!-- Enlaces a los archivos CSS de FullCalendar -->
    <link href="css/fullcalendar/main.min.css" rel="stylesheet" />
    <!-- Tu CSS personalizado para el calendario -->
    <link href="css/style.css" rel="stylesheet" />

    <style>
        /* Asegurarse de que html y body tomen la altura completa para que flexbox funcione */
        html, body {
            height: 100%; 
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Evita el scroll horizontal */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            flex-direction: column; /* Siempre apila elementos verticalmente */
            min-height: 100vh; /* Asegura que el body ocupe al menos la altura del viewport */
            box-sizing: border-box;
            padding-top: 60px; /* Espacio para barra superior */
        }

        /* Ocultar navegación antigua */
        .top-navbar, .sidebar {
            display: none;
        }

        /* Variables de color para consistencia */
        :root {
            --azul: #007bff;
            --azul-oscuro: #0056b3;
            --azul-claro: #d7e1ff;
            --blanco: #fff;
            --gris: #e9e9e9;
            --gris-fondo: #f5f5f5;
        }

        /* Sidebar dedicado para calendario */
        .calendar-sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1001;
            top: 0;
            left: 0;
            background-color: var(--azul);
            overflow-x: hidden;
            transition: width 0.3s ease;
            box-shadow: 10px 0 15px rgba(0, 0, 0, 0.3);
            border-right: none;
            padding-top: 60px;
        }

        .calendar-sidebar.open {
            width: 250px;
        }

        /* Botón de cerrar sidebar */
        .calendar-sidebar .closebtn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            background: none;
            border: none;
            color: var(--blanco);
            cursor: pointer;
            padding: 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .calendar-sidebar .closebtn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--blanco);
            transform: scale(1.1);
        }

        /* Información del usuario en sidebar */
        .calendar-sidebar .user-info {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            color: var(--blanco);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .calendar-sidebar .user-avatar {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: var(--blanco);
            flex-shrink: 0;
        }

        .calendar-sidebar .user-details {
            display: flex;
            flex-direction: column;
        }

        .calendar-sidebar .username {
            font-weight: bold;
            color: var(--blanco);
            font-size: 14px;
            margin-bottom: 2px;
        }

        .calendar-sidebar .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Navegación del sidebar */
        .calendar-sidebar .sidebar-nav {
            padding: 10px 0;
        }

        .calendar-sidebar .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: var(--blanco);
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            border-left: 3px solid transparent;
            font-size: 14px;
        }

        .calendar-sidebar .nav-item:hover {
            background-color: var(--azul-claro);
            color: #000;
            text-decoration: none;
            border-left-color: var(--blanco);
            transform: translateX(2px);
        }

        .calendar-sidebar .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--blanco);
            color: var(--blanco);
            font-weight: 600;
        }

        .calendar-sidebar .nav-item.logout {
            color: #ffcccc;
            margin-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
        }

        .calendar-sidebar .nav-item.logout:hover {
            background-color: rgba(220, 53, 69, 0.3);
            border-left-color: #dc3545;
            color: #ffcccc;
        }

        .calendar-sidebar .nav-icon {
            font-size: 16px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .calendar-sidebar .nav-text {
            font-size: 14px;
            font-weight: 500;
            flex: 1;
        }

        /* Barra superior personalizada para calendario */
        .calendar-top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--azul);
            padding: 5px 20px;
            box-shadow: 0px 4px 5px rgba(0, 0, 0, 0.3);
            z-index: 999;
            box-sizing: border-box;
        }

        .calendar-top-bar .menu-btn {
            background: var(--azul);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .calendar-top-bar .menu-btn:hover {
            background: var(--azul-oscuro);
            transform: scale(1.05);
        }

        .calendar-top-bar .top-bar-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .calendar-top-bar .top-bar-title h1 {
            margin: 0;
            font-size: 18px;
            color: white;
            font-weight: 500;
        }

        .calendar-top-bar .logo {
            height: 50px;
            width: auto;
            border-radius: 8px;
        }

        .calendar-top-bar .welcome-text {
            font-size: 14px;
            color: white;
        }


        /* --- Contenido Principal --- */
        /* Wrapper para el contenido principal y el panel lateral */
        .content-wrapper {
            display: flex;
            flex-grow: 1; /* Permite que ocupe el espacio disponible */
            box-sizing: border-box;
            width: 100%; /* Asegura que ocupe todo el ancho disponible */
            transition: margin-left 0.3s ease; /* Transición para el desplazamiento del contenido */
            margin-top: 0; /* Ya incluido en padding-top del body */
        }

        /* Cuando el sidebar está abierto */
        body.sidebar-open .content-wrapper {
            margin-left: 250px;
        }

        .main-content {
            flex-grow: 1; /* Ocupa el espacio restante */
            padding: 20px;
            transition: margin-right 0.3s ease; /* Para cuando el panel lateral se abra/cierre */
            box-sizing: border-box;
            display: flex; /* Convierte main-content en un contenedor flex */
            flex-direction: column; /* Apila el calendario y otros elementos verticalmente */
            justify-content: flex-start; /* Alinea el contenido a la parte superior */
            align-items: center; /* Centra el contenido horizontalmente */
            min-height: calc(100vh - 60px); /* Altura mínima: viewport - navbar (footer se maneja aparte) */
        }

        #calendar-container {
            width: 100%;
            max-width: 1200px; /* Limita el ancho máximo */
            margin: 0; /* Ya centrado por main-content */
            padding: 25px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            flex-grow: 1; /* Permite que el contenedor del calendario crezca */
            display: flex; /* Convierte calendar-container en un contenedor flex */
            flex-direction: column; /* Para que el calendario dentro pueda expandirse */
        }

        #calendar {
            flex-grow: 1; /* Permite que FullCalendar tome toda la altura disponible */
            width: 100%;
        }

        /* Estilos para el encabezado del calendario (título, botones de navegación) */
        .fc-toolbar-title {
            font-size: 2em;
            color: #333;
            font-weight: 700;
        }

        /* Estilos para los botones de FullCalendar */
        .fc-button {
            background-color: #3478F5; /* Azul del tema */
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .fc-button:hover {
            background-color: #2a6ed0; /* Tono más oscuro al pasar el ratón */
            transform: translateY(-2px);
        }

        .fc-button-active {
            background-color: #2a6ed0;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Estilos para los días de la semana en el encabezado */
        .fc-col-header-cell {
            background-color: #e0e0e0;
            color: #555;
            font-weight: 600;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        /* Estilos para las celdas de los días del calendario */
        .fc-daygrid-day {
            background-color: #fdfdfd;
            border: 1px solid #eee;
        }

        .fc-day-today {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
        }

        /* Estilos para los eventos */
        .fc-event {
            background-color: #2196F3; /* Azul primario para el color de los eventos */
            color: white;
            border: 1px solid #1976D2;
            border-radius: 5px;
            padding: 2px 5px;
            font-size: 0.85em;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .fc-event:hover {
            background-color: #1976D2;
        }

        /* Estilos para el contenedor de mensajes (sin Tailwind) */
        #message-container {
            position: fixed;
            top: 70px; /* Ajustado para no chocar con la navbar */
            right: 20px;
            z-index: 1000;
            width: 300px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid transparent;
            font-size: 0.9em;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Estilos para el modal de confirmación (sin Tailwind) */
        .confirm-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s ease;
        }

        .confirm-modal-overlay.visible {
            visibility: visible;
            opacity: 1;
        }

        .confirm-modal-content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .confirm-modal-content p {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .confirm-modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .confirm-modal-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .confirm-modal-buttons .confirm-ok {
            background-color: #4CAF50;
            color: white;
        }

        .confirm-modal-buttons .confirm-ok:hover {
            background-color: #45a049;
            transform: translateY(-1px);
        }

        .confirm-modal-buttons .confirm-cancel {
            background-color: #e0e0e0;
            color: #333;
        }

        .confirm-modal-buttons .confirm-cancel:hover {
            background-color: #c0c0c0;
            transform: translateY(-1px);
        }

        /* --- Estilos para el nuevo Panel de Detalles de Eventos --- */
        #event-details-panel {
            position: fixed;
            top: 60px; /* Espacio para la barra superior */
            right: -400px; /* Oculto por defecto */
            width: 380px; /* Ancho fijo para el panel lateral */
            height: calc(100vh - 60px);
            background-color: #f8f9fa;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-sizing: border-box;
            border-left: 1px solid #e0e0e0;
        }

        #event-details-panel.open {
            right: 0;
        }

        #event-details-panel h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #close-panel-btn {
            background: none;
            border: none;
            font-size: 1.8em;
            color: #666;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        #close-panel-btn:hover {
            color: #333;
        }

        #event-list {
            flex-grow: 1;
            overflow-y: auto;
            margin-top: 15px;
            padding-right: 5px;
        }

        .event-list-item {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        .event-list-item:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        .event-list-item.selected {
            background-color: #e6f7ff;
            border-color: #91d5ff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .event-list-item .event-title {
            font-weight: bold;
            color: #333;
            flex-grow: 1;
        }

        .event-list-item .event-time {
            font-size: 0.85em;
            color: #777;
            margin-left: 10px;
        }

        #event-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 10px;
            border: 1px solid #dee2e6;
            min-height: 100px;
            color: #495057;
            font-size: 0.95em;
            line-height: 1.6;
        }

        #event-summary h4 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        #event-summary p {
            margin-bottom: 8px;
        }

        /* Estilos para los botones de acción en el panel */
        .panel-actions {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .panel-actions button {
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .panel-actions .btn-add {
            background-color: #3478F5; /* Azul del tema */
            color: white;
        }
        .panel-actions .btn-add:hover {
            background-color: #2a6ed0;
            transform: translateY(-1px);
        }

        .panel-actions .btn-edit {
            background-color: #007bff;
            color: white;
        }
        .panel-actions .btn-edit:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .panel-actions .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .panel-actions .btn-delete:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        /* --- Estilos para el Modal de Agregar/Editar Evento --- */
        .event-form-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s ease;
        }

        .event-form-modal-overlay.visible {
            visibility: visible;
            opacity: 1;
        }

        .event-form-modal-content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 90%;
            text-align: left;
        }

        .event-form-modal-content h4 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 1.5em;
            text-align: center;
        }

        .event-form-modal-content .form-group {
            margin-bottom: 15px;
        }

        .event-form-modal-content label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .event-form-modal-content input[type="text"],
        .event-form-modal-content input[type="datetime-local"],
        .event-form-modal-content textarea,
        .event-form-modal-content select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .event-form-modal-content textarea {
            resize: vertical;
            min-height: 80px;
        }

        .event-form-modal-content .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .event-form-modal-content .modal-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .event-form-modal-content .modal-buttons .btn-submit {
            background-color: #3478F5; /* Azul del tema */
            color: white;
        }
        .event-form-modal-content .modal-buttons .btn-submit:hover {
            background-color: #2a6ed0;
        }

        .event-form-modal-content .modal-buttons .btn-cancel {
            background-color: #e0e0e0;
            color: #333;
        }
        .event-form-modal-content .modal-buttons .btn-cancel:hover {
            background-color: #c0c0c0;
        }

        /* --- Estilos del Footer --- */
        .footer {
            background-color: #3478F5; /* Mismo azul del tema */
            color: white;
            text-align: center;
            padding: 15px 20px;
            margin-top: auto; /* Empuja el footer hacia abajo */
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box; /* Incluye padding y border en el tamaño total */
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding-top: 20px;
            }
            
            #event-details-panel {
                width: 100%; 
                right: -100%; 
            }
            
            #event-details-panel.open {
                right: 0;
            }
            
            .footer {
                position: relative; 
                width: 100%;
                margin-left: 0;
            }
        }
        
        /* Asegurar compatibilidad con sidebar centralizado */
        body.sidebar-open .content-wrapper {
            margin-left: 250px;
        }
        
        @media (max-width: 768px) {
            body.sidebar-open .content-wrapper {
                margin-left: 0;
            }
        }

        @media (max-width: 480px) {
            .fc-toolbar {
                flex-direction: column;
                align-items: center;
            }
            .fc-toolbar-chunk {
                margin-bottom: 10px;
            }
        }

        /* Estilos para salones ocupados en el desplegable */
        .event-form-modal-content select option.occupied-salon {
            color: #999; /* Color gris para deshabilitados */
            background-color: #f0f0f0; /* Fondo ligeramente gris para indicar inactividad */
            font-style: italic;
        }
    </style>
</head>
<body>

<!-- Barra superior personalizada para calendario -->
<header class="calendar-top-bar">
    <button class="menu-btn" onclick="toggleCalendarSidebar()">☰ Menú</button>
    <div class="top-bar-title">
        <img src="img/logo.gif" alt="Logo CENEAC" class="logo" />
        <h1>Calendario</h1>
    </div>
    <div class="top-bar-actions">
        <?php 
        $currentUser = getCurrentUser();
        if ($currentUser): 
        ?>
            <span class="welcome-text">Bienvenido, <?= htmlspecialchars($currentUser['username']) ?></span>
        <?php endif; ?>
    </div>
</header>

<!-- Sidebar personalizado para calendario -->
<div id="calendar-sidebar" class="calendar-sidebar">
    <button class="closebtn" onclick="toggleCalendarSidebar()">&times;</button>
    
    <!-- Información del usuario -->
    <div class="user-info">
        <div class="user-avatar">👤</div>
        <div class="user-details">
            <?php 
            $currentUser = getCurrentUser();
            if ($currentUser): 
            ?>
                <span class="username"><?= htmlspecialchars($currentUser['username']) ?></span>
                <span class="user-role">Administrador</span>
            <?php else: ?>
                <span class="username">Usuario</span>
                <span class="user-role">Administrador</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Menú de navegación -->
    <nav class="sidebar-nav">
        <a href="<?= $basePath ?>admin/inicio/vista/inicio.php" class="nav-item">
            <span class="nav-icon">🏠</span>
            <span class="nav-text">Inicio</span>
        </a>
        <a href="<?= $basePath ?>admin/agregar_secre/vista/lista_secretaria.php" class="nav-item">
            <span class="nav-icon">👩‍💼</span>
            <span class="nav-text">Gestión de Secretarias</span>
        </a>
        <a href="<?= $basePath ?>admin/agregar_profe/vista/lista_profe.php" class="nav-item">
            <span class="nav-icon">👨‍🏫</span>
            <span class="nav-text">Gestión de Profesores</span>
        </a>
        <a href="<?= $basePath ?>admin/agregar_cursos/vista/lista_cursos.php" class="nav-item">
            <span class="nav-icon">📚</span>
            <span class="nav-text">Gestión de Cursos</span>
        </a>
        <a href="<?= $basePath ?>admin/agregar_salon/vista/lista_salon.php" class="nav-item">
            <span class="nav-icon">🏫</span>
            <span class="nav-text">Gestión de Salones</span>
        </a>
        <a href="<?= $basePath ?>admin/calendario_app/vista/calendario_principal.php" class="nav-item active">
            <span class="nav-icon">📅</span>
            <span class="nav-text">Calendario</span>
        </a>
        <a href="<?= $basePath ?>admin/configuracion/vista/configuracion.php" class="nav-item">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text">Configuración</span>
        </a>
        
        <!-- Cerrar sesión -->
        <a href="<?= str_repeat('../', substr_count($basePath, '../')) ?>verificacion/cerrar_sesion.php" class="nav-item logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-text">Cerrar Sesión</span>
        </a>
    </nav>
</div>

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
            <h4 id="event-form-modal-title">Agregar Nuevo Evento</h4>
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

    <!-- Carga de las librerías JavaScript de FullCalendar -->
    <script src='js/fullcalendar/index.global.min.js'></script>
    <script src='js/fullcalendar/locales/es.global.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data-10-year-range.min.js"></script>
    <script src='js/calendar_script.js'></script>
    
    <!-- JavaScript personalizado para el sidebar del calendario -->
    <script>
        let calendar; // Variable global para el calendario
        
        function toggleCalendarSidebar() {
            const sidebar = document.getElementById('calendar-sidebar');
            const body = document.body;
            
            if (sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                body.classList.remove('sidebar-open');
            } else {
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
            }
            
            // Actualizar el calendario después del toggle
            if (calendar && typeof calendar.updateSize === 'function') {
                setTimeout(() => {
                    calendar.updateSize();
                }, 300);
            }
        }

        // Crear overlay si no existe
        function createSidebarOverlay() {
            if (!document.getElementById('sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'sidebar-overlay';
                overlay.className = 'sidebar-overlay';
                overlay.addEventListener('click', function() {
                    toggleCalendarSidebar();
                });
                document.body.appendChild(overlay);
            }
        }

        // Cerrar sidebar al hacer click fuera (móvil)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('calendar-sidebar');
            const menuBtn = document.querySelector('.menu-btn');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('open')) {
                toggleCalendarSidebar();
            }
        });

        // Responsive behavior
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('calendar-sidebar');
            const body = document.body;
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
                if (overlay) overlay.classList.remove('active');
            } else {
                sidebar.classList.remove('open');
                body.classList.remove('sidebar-open');
                if (overlay) overlay.classList.remove('active');
            }
            
            // Actualizar calendario en cambio de tamaño
            if (calendar && typeof calendar.updateSize === 'function') {
                setTimeout(() => {
                    calendar.updateSize();
                }, 100);
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            createSidebarOverlay();
            
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('calendar-sidebar');
                const body = document.body;
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
            }
        });
    </script>
    
</body>
</html>
