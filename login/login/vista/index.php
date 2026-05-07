<?php
require_once '../../../core/Session.php';
require_once '../../../core/Security.php';

// Iniciar sesión segura
$session = getSession();
$session->start();

// Configuración de mensajes de error
$errorMessages = [
    'usuario_no_existe' => 'El usuario no existe en nuestro sistema',
    'contrasena_incorrecta' => 'Contraseña incorrecta para este usuario',
    'invalid_input' => 'Usuario y contraseña son requeridos (mínimo 8 caracteres)',
    'system_error' => 'Error del sistema. Por favor intente más tarde',
    'too_many_attempts' => 'Demasiados intentos fallidos. Intente más tarde',
    'login_failed' => 'Usuario o contraseña incorrectos',
    'access_denied' => 'Acceso denegado',
    'session_expired' => 'Sesión expirada. Por favor inicie sesión nuevamente'
];

$successMessages = [
    'logout_success' => 'Sesión cerrada correctamente',
    'registration_success' => 'Registro exitoso. Puede iniciar sesión',
    'recovery_sent' => 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación',
    'password_reset' => 'Contraseña restablecida exitosamente. Puede iniciar sesión'
];

$error = isset($_GET['error']) ? $_GET['error'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
$username = isset($_GET['user']) ? Security::sanitizeString($_GET['user']) : '';

// Generar token CSRF
$csrfToken = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistema de autenticación de Ceneac Producciones">
  <title>Ceneac | Inicio de Sesión</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="img/favicon.ico  " type="image/x-icon">
  <style>
    .alert {
      padding: 12px 16px;
      border-radius: 4px;
      margin-bottom: 20px;
      text-align: center;
      font-size: 14px;
    }
    .alert-danger {
      background-color: #ffebee;
      color: #c62828;
      border: 1px solid #ef9a9a;
    }
    .alert-warning {
      background-color: #fff8e1;
      color: #ff8f00;
      border: 1px solid #ffe082;
    }
    .alert-info {
      background-color: #e3f2fd;
      color: #1565c0;
      border: 1px solid #90caf9;
    }
    .forgot-password {
      margin-top: 10px;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <section class="container">
    <div class="login-box">
      <h2>Iniciar sesión</h2>

      <?php if (!empty($error) && isset($errorMessages[$error])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($errorMessages[$error]) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($message) && isset($successMessages[$message])): ?>
        <div class="alert alert-info">
          <?= htmlspecialchars($successMessages[$message]) ?>
        </div>
      <?php endif; ?>

      <form id="loginForm" action="../logica/sesion.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="textbox">
          <label for="usuarioInput">Usuario</label>
          <input type="text" name="usuario" id="usuarioInput" placeholder="Usuario" required
                 value="<?= htmlspecialchars($username) ?>">
        </div>
        
        <div class="textbox">
          <label for="passwordInput">Contraseña</label>
          <input type="password" name="contrasena" placeholder="Contraseña" required id="passwordInput" minlength="8">
          <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
            <img src="/proto/login/login/vista/img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon">
            <img src="/proto/login/login/vista/img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon" style="display: none;">
          </button>
        </div>
        
        <button type="submit" class="btn">Iniciar sesión</button>
        <div  id="formLoader" style="display:none;"></div>
      </form>
      
      <div class="links-container">
        <!-- <p class="message">¿No está registrado? <a href="../../registro/vista/registro.php">Crea una cuenta</a></p> -->
        <p class="forgot-password"><a href="#forgot-password" id="forgot-password-link">¿Olvidaste tu contraseña?</a></p>
      </div>
    </div>
    
    <div class="logo-container">
      <img src="/proto/login/login/vista/img/logoceneac.icon" alt="Logo Ceneac Producciones">
    </div>
  </section>

  <!-- Modal de recuperación de contraseña -->
  <div id="forgot-password-modal" class="modal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Recuperar contraseña</h2>
      <form id="recoveryForm" method="post" action="/proto/login/recuperacion/logica/recuperar.php">
        <div class="textbox-imail">
          <label for="emailInput">Correo electrónico</label>
          <input type="email" name="email" id="emailInput" placeholder="Correo electrónico" required
                 value="<?= htmlspecialchars($username) ?>">
        </div>
        <button type="submit" class="btn">Enviar enlace</button>
      </form>
      <p class="modal-message">Te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>
  </div>

  <footer class="footer">
    <div class="footer-links">
      <a href="/proto/inicio">Inicio</a>
      <a href="/proto/servicios">Servicios</a>
      <a href="/proto/portafolio">Portafolio</a>
      <a href="/proto/contacto">Contacto</a>
      <a href="/proto/terminos">Términos y condiciones</a>
      <a href="/proto/privacidad">Política de privacidad</a>
    </div>
    <div class="footer-copyright">
      © 2025 Ceneac Producciones. Todos los derechos reservados.
    </div>
  </footer>

  <script src="/proto/login/login/vista/script.js"></script>
  <!-- <script src="/proto/login/login/vista/depuracion.js"></script> -->

</body>
</html>