<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/secretaria_sidebar.php';
require_once '../logica/lobby_logica.php';

// Configurar página actual y ruta base
$currentPage = 'lobby';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lobby - CENEAC Secretaria</title>
  
  <!-- Estilos del sidebar centralizado - patrón estándar -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos del módulo lobby -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<!-- Botón para abrir el sidebar - patrón estándar -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado - patrón estándar -->
<?= renderSecretariaSidebar($currentPage, $basePath) ?>

  <main class="main-content">
    <h1>LOBBY</h1>
    <div class="header">
      <p>(panel de control o lista de estudiantes)</p>
      <button class="add-btn" id="openModalBtn">Agregar Estudiante</button>
    </div>

    <!-- Buscador -->
    <div class="buscador-container">
      <input type="text" id="buscar-estudiante" placeholder="Buscar por nombre, apellido o cédula...">
    </div>

    <!-- Contenedor de tabla -->
    <div class="table-container">
      <table class="student-table">
        <thead>
          <tr>
            <th>Curso</th>
            <th>C.I</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-estudiantes">
          <tr><td colspan="6">Cargando estudiantes...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div id="paginacion-estudiantes" class="paginacion"></div>
  </main>

  <!-- Modal para agregar estudiante -->
  <div id="studentModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="modal-title">Agregar Nuevo Estudiante</span>
        <span class="close-modal">&times;</span>
      </div>
      <form id="studentForm">
        <div class="form-group">
          <label for="course">Curso:</label>
          <select id="course" name="curso" required>
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos as $curso): ?>
              <option value="<?= htmlspecialchars($curso['id_cursos']) ?>">
                <?= htmlspecialchars($curso['nombre_curso']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="ci">Cédula de Identidad:</label>
          <input type="text" id="ci" name="cedula" required />
        </div>
        <div class="form-group">
          <label for="firstName">Nombre:</label>
          <input type="text" id="firstName" name="nombre" required />
        </div>
        <div class="form-group">
          <label for="lastName">Apellido:</label>
          <input type="text" id="lastName" name="apellido" required />
        </div>
        <div class="form-group">
          <label for="phone">Teléfono:</label>
          <input type="tel" id="phone" name="telefono" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <script src="script.js"></script>
  <script src="botones.js"></script>
  <script src="lobby_pagination.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSecretariaSidebarScript() ?>

</body>
</html>
