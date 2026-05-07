<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';

// Configurar página actual y ruta base
$currentPage = 'secretarias_retiradas';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Historial de Secretarias Retiradas - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="modal.css" />
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Historial de Secretarias Retiradas', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

  <!-- Contenido principal -->
  <main class="contenido">
    <h1>Historial de Secretarias Retiradas</h1>

    <!-- Buscador -->
    <div class="buscador-container">
      <input type="text" id="buscar-secre-ret" placeholder="Buscar por nombre, apellido o cédula...">
    </div>

    <!-- Tabla -->
    <table border="1" class="tabla-secre">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Cédula</th>
          <th>Teléfono</th>
          <th>Correo</th>
          <th>Razón</th>
          <th>Fecha Retiro</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-retiradas">
        <tr><td colspan="7">Cargando secretarias retiradas...</td></tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div id="paginacion-ret" class="paginacion"></div>
  </main>

  <!-- Pie de página -->
  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <!-- Modal Reintegrar Secretaria -->
  <div id="modal-reintegrar" class="modal-overlay">
    <div class="modal-content">
      <button class="close-btn" id="cerrar-modal-reintegrar">&times;</button>
      <h2>Reintegrar Secretaria</h2>
      <form id="form-reintegrar-secre">
        <input type="hidden" id="reintegrar-id-secre" name="id_secre">

        <p>¿Deseas reintegrar esta secretaria al listado principal?</p>

        <div class="modal-acciones">
          <button type="submit">Reintegrar</button>
          <button type="button" id="cancelar-reintegrar">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="secretarias_retiradas.js"></script>
  <script src="script.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSidebarScript() ?>

</body>
</html>
