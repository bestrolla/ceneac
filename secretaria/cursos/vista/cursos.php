
<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/secretaria_sidebar.php';
require_once '../../../BBDD/BBDD.php';
require_once '../logica/cursos_logica.php';

try {
    $database = new Database();
    $db = $database->connect();

    // Cambia esta línea para pasar $db (PDO) y no $database (Database)
    $cursosLogica = new CursosLogica($db);

    $cursos = $cursosLogica->obtenerTodosLosCursos();
    $error_message = "";
} catch (Exception $e) {
    $cursos = [];
    $error_message = "Error al cargar los cursos.";
}

// Configurar página actual y ruta base
$currentPage = 'cursos';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lista de Cursos - CENEAC Secretaria</title>
  
  <!-- Estilos del sidebar centralizado - patrón estándar -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos del módulo cursos -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<!-- Botón para abrir el sidebar - patrón estándar -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado - patrón estándar -->
<?= renderSecretariaSidebar($currentPage, $basePath) ?>


<h1 class="subtitle">Lista de cursos</h1>
<div class="buscador-container">
  <input type="text" id="buscar-cursos" placeholder="Buscar por nombre de curso...">
</div>
<main class="main-content">
    <?php if (!empty($error_message)): ?>
      <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- Buscador -->

    <!-- Contenedor de cursos -->
    <div id="cursos-container">
      <p>Cargando cursos...</p>
    </div>

    <!-- Paginación -->
    <div id="paginacion-cursos" class="paginacion"></div>
</main>

  <!-- Modal -->
  <div id="modal-estudiantes" class="modal">
    <div class="modal-content">
      <span id="modal-close" class="modal-close">&times;</span>
      <h2>Lista de estudiantes</h2>
      <ul id="lista-estudiantes-modal"></ul>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>
<script src="script.js"></script>
<script src="botones.js"></script>
<script src="cursos_pagination.js"></script>

<!-- JavaScript del sidebar centralizado -->
<?= renderSecretariaSidebarScript() ?>

</body>
</html>
