<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

// Configurar página actual y ruta base
$currentPage = 'configuracion';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuración - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css">
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Configuración', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

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
        <img src="/proto/login/login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon1">
        <img src="/proto/login/login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon1" style="display: none;">
      </button>
    </div>

    <!-- Campo Confirmar contraseña -->
    <div class="textbox">
      <label for="confirmar_contrasena">Confirmar contraseña:</label>
      <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8">
      <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
        <img src="/proto/login/login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon2">
        <img src="/proto/login/login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon2" style="display: none;">
      </button>
    </div>

    <button type="submit">Guardar cambios</button>
  </form>
</section>


    </section>
  </main>

  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <script src="script.js"></script>
  <script src="botones.js"></script>
  <script src="depuracion.js"></script>
  
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
    const buttons = document.querySelectorAll('button, .toggle-password');
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
