<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/clases_logica.php';
require_once '../../../BBDD/BBDD.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

$logica = new CursoLogica();
$cursosActivos = $logica->obtenerCursosActivos();

$db = new Database();
$conn = $db->connect();

// Configurar página actual y ruta base
$currentPage = 'inicio';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Programar Curso - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado - estilo calendario -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon" />
</head>
<body>

<!-- Botón personalizado para abrir el sidebar - estilo calendario -->
<?= renderAdminTopBar('Programar Curso', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

<section class="contenedor-principal">
  <main class="contenido">
    <h1>Programar Curso</h1>

   <form id="formProgramarCurso" method="POST" action="../logica/programar_cursos.php" class="form-programar">
  <div class="form-group">
    <label for="curso">Curso:</label>
    <select name="curso" id="curso" required>
      <option value="">Seleccione un curso</option>
      <?php foreach ($cursosActivos as $curso): ?>
        <option value="<?= htmlspecialchars($curso['id_cursos']) ?>">
          <?= htmlspecialchars($curso['nombre_curso'] . ' - Nivel ' . $curso['nivel_cursos']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
<div class="form-group">
  <label for="profesor">Profesor:</label>
  <select name="profesor" id="profesor" required>
    <option value="">Seleccione un profesor</option>
    <!-- Aquí se llena por JS -->
  </select>
</div>




      <div class="form-group">
        <label for="horario">Horario:</label>
        <select name="horario" id="horario" required>
          <option value="">Seleccione un horario</option>
          <?php
          $horarios = [
            ["Lunes,Miércoles,Viernes", "Mañana", "09:00 - 13:00"],
            ["Lunes,Miércoles,Viernes", "Tarde", "13:00 - 17:00"],
            ["Martes,Jueves", "Mañana", "09:00 - 13:00"],
            ["Martes,Jueves", "Tarde", "13:00 - 17:00"],
            ["Sábado", "Mañana", "08:00 - 13:00"],
            ["Sábado", "Tarde", "13:00 - 18:00"],
          ];
          foreach ($horarios as $h):
            $value = htmlspecialchars("{$h[0]}|{$h[1]}|{$h[2]}");
            $label = htmlspecialchars("{$h[0]} - {$h[1]} ({$h[2]})");
          ?>
            <option value="<?= $value ?>"><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="fecha_inicio">Fecha de inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required value="<?= date('Y-m-d') ?>" />
      </div>

      <div class="form-group">
        <small style="color: #666; font-style: italic;">
          ℹ️ La duración del curso se obtiene automáticamente de la base de datos según el curso seleccionado.
        </small>
      </div>

      <button type="submit" class="btn-submit">Programar Curso</button>
    </form>

    <h2>Cursos Programados</h2>

    <div class="table-container">
    <table class="tabla-programados">
      <thead>
        <tr>
          <th>Curso</th>
          <th>Días</th>
          <th>Turno</th>
          <th>Horario</th>
          <th>Fecha Inicio</th>
          <th>Fecha Fin</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("
          SELECT c.id_calendario, cu.nombre_curso, cu.nivel_cursos, c.dias, c.horario, 
                 c.fecha_inicio, c.fecha_fin
          FROM calendario c
          JOIN cursos cu ON c.id_cursos = cu.id_cursos
          ORDER BY c.fecha_inicio DESC
        ");
        $stmt->execute();
        $programados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($programados)):
        ?>
          <tr><td colspan="7">No hay cursos programados.</td></tr>
        <?php
        else:
          foreach ($programados as $fila):
            $nombreCurso = htmlspecialchars($fila['nombre_curso'] . " - Nivel " . $fila['nivel_cursos']);
            $dias = htmlspecialchars($fila['dias']);
            $horario = htmlspecialchars($fila['horario']);
            $fechaInicio = htmlspecialchars($fila['fecha_inicio']);
            $fechaFin = htmlspecialchars($fila['fecha_fin']);

            // Extraer turno (mañana o tarde) basado en la hora inicio del horario
            $horaInicio = explode(" - ", $fila['horario'])[0];
            $turno = (strtotime($horaInicio) < strtotime('12:00')) ? 'Mañana' : 'Tarde';
        ?>
          <tr>
            <td><?= $nombreCurso ?></td>
            <td><?= $dias ?></td>
            <td><?= $turno ?></td>
            <td><?= $horario ?></td>
            <td><?= $fechaInicio ?></td>
            <td><?= $fechaFin ?></td>
            <td>
              <form method="POST" action="../logica/eliminar_programacion.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta programación?');">
                <input type="hidden" name="id_calendario" value="<?= htmlspecialchars($fila['id_calendario']) ?>" />
                <button type="submit" class="btn-delete">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    </div>
  </main>
</section>

<footer>
  <p>© 2025 CENEAC. Todos los derechos reservados.</p>
</footer>

<script src="script.js"></script>
<script src="botones.js"></script>
<script src="panel.js"></script>

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
    const buttons = document.querySelectorAll('button, .btn-submit, .btn-delete');
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
