<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ceneac | Registro</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="shortcut icon" href="img/logoceneac.png" type="image/x-icon">
</head>
<body>
  <div class="page-wrapper">
    <section class="container">
      <div class="registro">
        <div class="progress-bar">
          <div class="progress-step active" data-step="1">1</div>
          <div class="progress-step" data-step="2">2</div>
          <div class="progress-step" data-step="3">3</div>
        </div>
        
        <form id="registroForm" action="../logica/registro.php" method="POST">
          <!-- Paso 1: Información personal -->
          <div class="form-page active" data-page="1">
            <h2>Información Personal</h2>
            <div class="input-group">
              <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
              <span class="error-message"></span>
            </div>
            <div class="input-group">
              <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
              <span class="error-message"></span>
            </div>
            <div class="input-group">
              <input type="text" id="cedula" name="cedula" placeholder="Cédula" required>
              <span class="error-message"></span>
            </div>
            <div class="input-group">
              <label for="fecha-nacimiento">Fecha de nacimiento</label>
              <input type="date" id="fecha-nacimiento" name="fecha-nacimiento" required>
              <span class="error-message"></span>
            </div>
            <div class="form-navigation">
              <button type="button" class="next-btn">Siguiente</button>
            </div>
          </div>

          <!-- Paso 2: Información de contacto -->
          <div class="form-page" data-page="2">
            <h2>Información de Contacto</h2>
            <div class="input-group">
              <input type="text" id="telefono" name="telefono" placeholder="Teléfono" required>
              <span class="error-message"></span>
            </div>
            <div class="input-group">
              <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required>
              <span class="error-message"></span>
            </div>
            <div class="form-navigation">
              <button type="button" class="prev-btn">Anterior</button>
              <button type="button" class="next-btn">Siguiente</button>
            </div>
          </div>

      <!-- Paso 3: Información de acceso -->
      <div class="form-page" data-page="3">
        <h2>Información de Acceso</h2>
        <p class="instruction">El usuario debe tener al menos 6 caracteres y contener letras y números.</p>
        <div class="input-group">
          <input type="text" id="usuario" name="usuario" placeholder="Usuario" required>
          <span class="error-message"></span>
        </div>
        <p class="instruction">La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una letra minúscula, un número y un carácter especial.</p>

        <!-- Contraseña con icono -->
        <div >
          <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>
          <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
            <img src="img/eye.svg" alt="Mostrar contraseña" class="password-icon ">
            <img src="img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon " style="display: none;">
          </button>
          <span class="error-message"></span>
        </div>

        <!-- Confirmar contraseña con icono -->
        <div >
          <input type="password" id="confirmar-contrasena" name="confirmar-contrasena" placeholder="Confirmar contraseña" required>
          <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
            <img src="img/eye.svg" alt="Mostrar contraseña" class="password-icon ">
            <img src="img/eye-slash.svg" alt="Ocultar contraseña" class="password-icon " style="display: none;">
          </button>
          <span class="error-message"></span>
        </div>

        <div class="form-navigation">
          <button type="button" class="prev-btn">Anterior</button>
          <input type="submit" class="submit-btn" value="registrar">
        </div>
      </div>


        </form>
      </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="footer-content">
        <div class="footer-section">
          <h3>Sobre Ceneac</h3>
          <p>Plataforma de educación en línea dedicada a proporcionar cursos de calidad para el desarrollo profesional.</p>
        </div>
        <div class="footer-section">
          <h3>Contacto</h3>
          <p>Email: info@ceneac.com</p>
          <p>Teléfono: +123 456 7890</p>
        </div>
        <div class="footer-section">
          <h3>Enlaces rápidos</h3>
          <ul>
            <li><a href="/">Inicio</a></li>
            <li><a href="/cursos">Cursos</a></li>
            <li><a href="/contacto">Contacto</a></li>
            <li><a href="/politica-privacidad">Política de Privacidad</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2023 Ceneac. Todos los derechos reservados.</p>
      </div>
    </footer>
  </div>

  <script src="script.js"></script>
</body>
</html>