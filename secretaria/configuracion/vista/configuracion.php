<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/secretaria_sidebar.php';

// Configurar página actual y ruta base
$currentPage = 'configuracion';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuración - CENEAC Secretaria</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderSecretariaTopBar('Configuración', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderSecretariaSidebar($currentPage, $basePath) ?>

  <!-- Contenido principal -->
  <main class="main-content">
    <h1>Configuración</h1>
    <div class="header">
    
    </div>

    <!-- Panel de configuración para cambiar usuario y contraseña -->
   <section class="config-panel">
  <h2>Cambiar nombre de usuario y contraseña</h2>
  <form action="../logica/config_logica.php" method="POST" class="config-form" id="configForm">
    
    <label for="nuevo_usuario">Nuevo nombre de usuario:</label>
    <input type="text" id="nuevo_usuario" name="nuevo_usuario" required>

    <!-- Campo Nueva contraseña -->
    <div class="textbox">
      <label for="nueva_contrasena">Nueva contraseña:</label>
      <input type="password" id="nueva_contrasena" name="nueva_contrasena" required minlength="8">
      <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
        <img src="/login/login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon1">
        <img src="/login/login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon1" style="display: none;">
      </button>
    </div>

    <!-- Campo Confirmar contraseña -->
    <div class="textbox">
      <label for="confirmar_contrasena">Confirmar contraseña:</label>
      <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8">
      <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
        <img src="/login/login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon2">
        <img src="/login/login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon2" style="display: none;">
      </button>
    </div>

    <button type="submit">Guardar cambios</button>
  </form>

    </section>
  </main>

  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <script src="script.js"></script>
  <script src="botones.js"></script>
  <script src="depuracion.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSecretariaSidebarScript() ?>

</body>
</html>
