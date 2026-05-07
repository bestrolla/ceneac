<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/obtener_salon.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

$logica = new SalonLogica();
$salonesSeparados = $logica->obtenerSalonesSeparados();

$salonesActivos = $salonesSeparados['activo'];
$salonesInactivos = $salonesSeparados['inactivo'];

// Configurar página actual y ruta base
$currentPage = 'salon';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Salones - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Gestión de Salones', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

<section class="contenedor-principal">
  <main class="contenido">
    
    <form id="form-salon" method="POST" action="../logica/agregar_salon.php" class="formulario-salon">
      <h1>Gestión de Salones</h1>
      <input type="text" name="nombre" placeholder="Nombre del salón" required />
      <input type="number" name="matricula" placeholder="Capacidad (matrícula)" min="1" required />
      <button type="submit">Agregar Salón</button>
    </form>

    <!-- Tabla Salones Activos -->
    <h2>Salones Activos</h2>
    <div class="table-container">
    <table border="1" class="tabla-cursos">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Matrícula</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-salones">
        <?php if (!empty($salonesActivos)) : ?>
          <?php foreach ($salonesActivos as $s) : ?>
            <tr data-id="<?= htmlspecialchars($s['id_salon']) ?>">
              <td><?= htmlspecialchars($s['nombre_salon']) ?></td>
              <td><?= htmlspecialchars($s['matricula']) ?></td>
              <td><?= htmlspecialchars($s['status']) ?></td>
              <td>
                <button
                      class="btn-editar"
                      data-id="<?= $s['id_salon'] ?>"
                      data-nombre="<?= htmlspecialchars($s['nombre_salon']) ?>"
                      data-matricula="<?= htmlspecialchars($s['matricula']) ?>"
                    >
                      Editar
                    </button>

                <button class="btn-inactivar" data-id="<?= $s['id_salon'] ?>">Desactivar</button>

                <button data-id="<?= $s['id_salon'] ?>" class="btn-eliminar">
                  eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr><td colspan="4">No hay salones activos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>

    <!-- Tabla Salones Inactivos -->
    <h2>Salones Inactivos</h2>
    <div class="table-container">
    <table border="1" class="tabla-cursos">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Matrícula</th>
          <th>Estado</th>
          <th>Motivo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-salones-inactivos">
        <?php if (!empty($salonesInactivos)) : ?>
          <?php foreach ($salonesInactivos as $s) : ?>
            <tr data-id="<?= htmlspecialchars($s['id_salon']) ?>">
              <td><?= htmlspecialchars($s['nombre_salon']) ?></td>
              <td><?= htmlspecialchars($s['matricula']) ?></td>
              <td><?= htmlspecialchars($s['status']) ?></td>
              <th><?= htmlspecialchars($s['motivo'] ?? '') ?></th>

              <td>
                <button
                  class="btn-editar"
                  data-id="<?= $s['id_salon'] ?>"
                  data-nombre="<?= htmlspecialchars($s['nombre_salon']) ?>"
                  data-matricula="<?= htmlspecialchars($s['matricula']) ?>"
                >
                  Editar
                </button>

                <button class="btn-activar" data-id="<?= $s['id_salon'] ?>">Activar</button>
                <button data-id="<?= $s['id_salon'] ?>" class="btn-eliminar">
                  eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr><td colspan="4">No hay salones inactivos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </main>

</section>
<div id="modal-editar" class="modal">
  <div class="modal-content">
    <span id="modal-close" class="close-modal">&times;</span>
    <h2>Editar Salón</h2>
    <form id="form-editar-salon" method="POST" action="../logica/editar_salon.php">
      <input type="hidden" id="id_salon" name="id_salon">

      <label for="nombre_salon">Nombre del salón:</label>
      <input type="text" id="nombre_salon" name="nombre" required>

      <label for="matricula">Capacidad (matrícula):</label>
      <input type="number" id="matricula" name="matricula" min="1" required>

      <br>
      <br>
      <button type="submit" id="btn-guardar">Actualizar Salón</button>
    </form>
  </div>
</div>
<div id="modal-motivo" class="modal">
  <div class="modal-content">
    <span id="modal-motivo-close" class="close-modal">&times;</span>
    <h2>Motivo de desactivación</h2>
    <form id="form-motivo" action="../logica/cambiar_estado_salon.php">
      <input type="hidden" id="motivo-id-salon" name="id">
      Ingrese el motivo por el cual se esta desactivando el salon:
      <br>
      <textarea id="motivo-texto" name="motivo" rows="4" required></textarea>
      <br>
      <button type="submit">Confirmar</button>
    </form>
  </div>
</div>
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
    const buttons = document.querySelectorAll('button, .btn-editar, .btn-inactivar, .btn-activar, .btn-eliminar');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Cerrar modales con tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
    
    // Prevenir scroll del body cuando los modales están abiertos
    const modalCloseButtons = document.querySelectorAll('.close-modal');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.body.style.overflow = 'auto';
        });
    });
});
</script>

</body>
</html>
