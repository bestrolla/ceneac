<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/obtener_curso.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

$logica = new CursosLogica();
$cursosSeparados = $logica->obtenerCursosSeparados();

$cursosActivos = $cursosSeparados['activos'];
$cursosInactivos = $cursosSeparados['inactivos'];

// Configurar página actual y ruta base
$currentPage = 'cursos';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Cursos - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="shortcut icon" href="/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Gestión de Cursos', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

  <section class="contenedor-principal">
    <main class="contenido">
      <h1>Gestión de Cursos</h1>

        <form id="form-curso" method="POST" action="../logica/agregar_cursos.php">
          <input type="text" name="nombre" placeholder="Nombre del curso" required />
          <input type="text" name="descripcion" placeholder="Descripción" />
          <input type="text" name="duracion" placeholder="Duración" required />
          <input type="text" name="nivel" placeholder="Nivel" required />
          <button type="submit">Agregar Curso</button>
        </form>


      <!-- Tabla de Cursos Activos -->
      <h2>Cursos Activos</h2>
      <div class="table-container">
      <table border="1" class="tabla-cursos">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Duración</th>
            <th>Nivel</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-cursos">
          <?php if (!empty($cursosActivos)) : ?>
            <?php foreach ($cursosActivos as $c) : ?>
              <tr data-id="<?= htmlspecialchars($c['id_cursos']) ?>">
                <td><?= htmlspecialchars($c['nombre_curso']) ?></td>
                <td><?= htmlspecialchars($c['descripcion'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['duracion'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['nivel_cursos']) ?></td>
                <td><?= htmlspecialchars($c['status']) ?></td>
                <td>
                  <button class="btn-inactivar" data-id="<?= htmlspecialchars($c['id_cursos']) ?>">Desactivar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="6">No hay cursos activos.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>

      <!-- Tabla de Cursos Inactivos -->
      <h2>Cursos Inactivos</h2>
      <div class="table-container">
      <table border="1" class="tabla-cursos">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Duración</th>
            <th>Nivel</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-cursos-inactivos">
          <?php if (!empty($cursosInactivos)) : ?>
            <?php foreach ($cursosInactivos as $c) : ?>
              <tr data-id="<?= htmlspecialchars($c['id_cursos']) ?>">
                <td><?= htmlspecialchars($c['nombre_curso']) ?></td>
                <td><?= htmlspecialchars($c['descripcion'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['duracion'] ?? '') ?></td>
                <td><?= htmlspecialchars($c['nivel_cursos']) ?></td>
                <td><?= htmlspecialchars($c['status']) ?></td>
                <td>
                  <button class="btn-activar" data-id="<?= htmlspecialchars($c['id_cursos']) ?>">Activar</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="6">No hay cursos inactivos.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>


    </main>
  </section>

  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <script src="botones.js"></script>
  <script src="script.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSidebarScript() ?>
  
  <script>
// Mejorar accesibilidad táctil en dispositivos móviles
document.addEventListener('DOMContentLoaded', function() {
    // Agregar clase para dispositivos táctiles
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    // Mejorar interacción con botones en móvil
    const buttons = document.querySelectorAll('button, .btn-inactivar, .btn-activar');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
  
</body>
</html>
