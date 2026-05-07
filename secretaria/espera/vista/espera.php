
<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/secretaria_sidebar.php';
require_once '../logica/espera_logica.php';

// Configurar página actual y ruta base
$currentPage = 'espera';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lista de Espera - CENEAC Secretaria</title>
  
  <!-- Estilos del sidebar centralizado - patrón estándar -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos del módulo espera -->
  <link rel="stylesheet" href="styles.css" />
  <style>
    /* Estilos para tabla y botones */
    .student-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .student-table th, .student-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    .student-table th {
      background-color: #f2f2f2;
    }
    .student-table tr:hover {
      background-color: #f5f5f5;
    }
    .btn-action {
      background: none;
      border: none;
      cursor: pointer;
      padding: 5px;
      margin: 0 3px;
    }
    .btn-action img {
      width: 20px;
      height: 20px;
    }
    .btn-move-lobby {
      color: blue;
    }
    .btn-delete {
      color: red;
    }
    
    /* Estilos para búsqueda y paginación */
    .buscador-container {
      margin: 20px 0;
      display: flex;
      justify-content: center;
    }
    
    .buscador-container input {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 25px;
      width: 300px;
      font-size: 16px;
      outline: none;
      transition: border-color 0.3s;
    }
    
    .buscador-container input:focus {
      border-color: #3478F5;
      box-shadow: 0 0 5px rgba(52, 120, 245, 0.3);
    }
    
    .table-container {
      margin: 20px 0;
      overflow-x: auto;
    }
    
    .paginacion {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin: 20px 0;
      padding: 20px 0;
    }
    
    .paginacion button {
      padding: 8px 16px;
      border: 1px solid #ddd;
      background-color: white;
      color: #3478F5;
      border-radius: 4px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .paginacion button:hover:not(:disabled) {
      background-color: #3478F5;
      color: white;
    }
    
    .paginacion button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .paginacion span {
      font-weight: bold;
      color: #3478F5;
    }
    
    /* Toast styles */
    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-20px); }
      10% { opacity: 1; transform: translateY(0); }
      90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-20px); }
    }
  </style>
</head>
<body>

<!-- Botón para abrir el sidebar - patrón estándar -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado - patrón estándar -->
<?= renderSecretariaSidebar($currentPage, $basePath) ?>

<main class="main-content">
  <h1>Lista de Espera</h1>
  <p>Estudiantes en estado <strong>espera</strong> para asignación a curso.</p>

  <!-- Buscador -->
  <div class="buscador-container">
    <input type="text" id="buscar-espera" placeholder="Buscar por nombre, apellido o cédula...">
  </div>

  <!-- Contenedor de tabla -->
  <div class="table-container">
    <table class="student-table">
      <thead>
        <tr>
          <th>C.I</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Teléfono</th>
          <th>Curso</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-espera">
        <tr><td colspan="6">Cargando estudiantes...</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Paginación -->
  <div id="paginacion-espera" class="paginacion"></div>
</main>

<footer>
  <p>© 2025 CENEAC. Todos los derechos reservados.</p>
</footer>

<script src="botones.js"></script>
<script src="script.js"></script>
<script src="espera_pagination.js"></script>

<!-- JavaScript del sidebar centralizado -->
<?= renderSecretariaSidebarScript() ?>

</body>
</html>
