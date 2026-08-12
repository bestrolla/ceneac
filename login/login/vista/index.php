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
    'invalid_input' => 'Usuario y contraseña son requeridos',
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
  <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
  
  <!-- Fuente manuscrita para la nota de prueba -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap" rel="stylesheet">

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

    /* =========================================
       ESTILOS PARA LA NOTA ADHESIVA EXTERNA
       ========================================= */
    .page-wrapper {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Nota adhesiva flotante (Sticky Note) */
    .sticky-note {
      position: absolute;
      left: calc(50% + 280px); /* Ajusta la posición horizontal respecto al centro */
      top: 20px;
      width: 230px;
      padding: 20px 18px;
      background: #fef08a; /* Color Post-it */
      color: #713f12;
      border-radius: 2px 2px 25px 2px; /* Detalle de esquina doblada */
      box-shadow: 4px 6px 12px rgba(0, 0, 0, 0.15);
      transform: rotate(3deg); /* Inclinación de nota real */
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      z-index: 100;
      font-family: 'Caveat', cursive, sans-serif;
    }

    .sticky-note:hover {
      transform: rotate(0deg) scale(1.03);
      box-shadow: 6px 10px 18px rgba(0, 0, 0, 0.22);
    }

    /* Chincheta / Pin rojo */
    .sticky-note::before {
      content: '';
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 14px;
      height: 14px;
      background: #ef4444;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .sticky-note h3 {
      margin: 0 0 8px 0;
      font-size: 1.35rem;
      text-align: center;
      border-bottom: 1px dashed #ca8a04;
      padding-bottom: 4px;
    }

    .sticky-note ul {
      margin: 0;
      padding-left: 15px;
      font-size: 1.15rem;
    }

    .sticky-note li {
      margin-bottom: 4px;
    }

    .sticky-note code {
      font-family: monospace;
      background-color: rgba(255, 255, 255, 0.65);
      padding: 1px 5px;
      border-radius: 3px;
      font-weight: bold;
    }

    /* Adaptación fluida para pantallas pequeñas y celulares */
    @media (max-width: 992px) {
      .page-wrapper {
        flex-direction: column-reverse;
      }
      .sticky-note {
        position: relative;
        left: auto;
        top: auto;
        margin: 20px auto;
        transform: rotate(-1deg);
        width: 90%;
        max-width: 350px;
      }
    }
  </style>
</head>
<body>

  <!-- Wrappea la sección para dar contexto relativo a la nota -->
  <div class="page-wrapper">

    <!-- 📌 NOTA ADHESIVA FUERA DEL CONTAINER -->
    <aside class="sticky-note">
      <h3>📌 Credenciales de prueba</h3>
      <ul>
        <li><strong>Usuario:</strong> <code>admin</code></li>
        <li><strong>Password:</strong> <code>123456</code></li>
      </ul>
    </aside>

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
            <input type="text" name="usuario" id="usuarioInput" placeholder="Usuario"
                   value="<?= htmlspecialchars($username) ?>">
          </div>
          
          <div class="textbox">
            <label for="passwordInput">Contraseña</label>
            <input type="password" name="contrasena" placeholder="Contraseña" id="passwordInput">
            <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
              <img src="img/eye.svg" alt="Mostrar contraseña" class="password-icon" id="showPasswordIcon">
              <img src="img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon" id="hidePasswordIcon" style="display: none;">
            </button>
          </div>
          
          <button type="submit" class="btn">Iniciar sesión</button>
          <div id="formLoader" style="display:none;"></div>
        </form>
        
        <div class="links-container">
          <!-- <p class="message">¿No está registrado? <a href="../../registro/vista/registro.php">Crea una cuenta</a></p> -->
          <p class="forgot-password"><a href="#forgot-password" id="forgot-password-link">¿Olvidaste tu contraseña?</a></p>
        </div>
      </div>
      
      <div class="logo-container">
        <img src="img/logoceneac.icon" alt="Logo Ceneac Producciones">
      </div>
    </section>

  </div> <!-- /.page-wrapper -->

  <!-- Modal de recuperación de contraseña -->
  <div id="forgot-password-modal" class="modal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Recuperar contraseña</h2>
      <form id="recoveryForm" method="post" action="../../recuperacion/logica/recuperar.php">
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
      <a href="../../../inicio">Inicio</a>
      <a href="../../../servicios">Servicios</a>
      <a href="../../../portafolio">Portafolio</a>
      <a href="../../../contacto">Contacto</a>
      <a href="../../../terminos">Términos y condiciones</a>
      <a href="../../../privacidad">Política de privacidad</a>
    </div>
    <div class="footer-copyright">
      © 2025 Ceneac Producciones. Todos los derechos reservados.
    </div>
  </footer>

  <script src="script.js"></script>
  <!-- <script src="depuracion.js"></script> -->

</body>
</html>