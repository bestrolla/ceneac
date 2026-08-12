 <?php 
require_once '../logica/inicio_logica.php';
require_once '../../../verificacion/verificar_acceso.php';
verificarAcceso('administrador');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lobby | Administrador</title>
  <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="/login/login/vista/img/favicon.ico" type="image/x-icon">

</head>
<body>

  <!-- Encabezado superior con barra y logo alineados horizontalmente -->
  <header class="top-bar">
    <button class="openbtn" onclick="toggleSidebar()">☰ Menú</button>
    <img src="img/logo.gif" alt="Logo CENEAC" class="logo">
  </header>

  <!-- Sidebar retráctil al lado izquierdo -->
  <div id="sidebar" class="sidebar left">
    <button class="closebtn" onclick="toggleSidebar()">×</button>
    <a href="../../inicio/vista/inicio.php">Inicio</a>
    <a href="../../agregar_secre/vista/lista_secretaria.php">Lista de secretarias</a>
    <a href="../../agregar_profe/vista/lista_profe.php">Lista de profesores</a>
    <a href="../../calendario/vista/index.php">Calendario</a>
    <a href="../../agregar_cursos/vista/lista_cursos.php">Agregar Cursos</a>
    <a href="../../agregar_salon/vista/lista_salon.php">Agregar Salón</a>
    <a href="../../configuracion/vista/configuracion.php">Configuración</a>

     <a  href="../../../verificacion/cerrar_sesion.php">Cerrar sesión</a>
  </div>
  <!-- Contenido principal -->
 

  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <script src="script.js"></script>
  <script src="botones.js">  </script>


  
</body>
</html>
