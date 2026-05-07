<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

// Configurar página actual y ruta base
$currentPage = 'secretarias';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Secretarias - CENEAC Admin</title>
  
  <!-- Estilos del sidebar centralizado -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
  
  <!-- Estilos responsive centralizados -->
  <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
  
  <!-- Estilos específicos de la página -->
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="modal.css" />
  <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Botón para abrir el sidebar - patrón estándar -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado - patrón estándar -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

  <!-- Contenido principal -->
  <main class="contenido">
    <h1>Gestión de Secretarias</h1>

    <!-- Formulario Agregar -->
    <form id="form-secre">
      <input type="text" name="nombre" placeholder="Nombre" required />
      <input type="text" name="apellido" placeholder="Apellido" required />
      <input type="text" name="cedula" placeholder="Cédula" required />
      <input type="text" name="telefono" placeholder="Teléfono" required />
      <input type="email" name="correo" placeholder="Correo" required />
      <button type="submit">Agregar Secretaria</button>
    </form>


    <!-- Buscador -->
    <div class="buscador-container">
      <input type="text" id="buscar-secre" placeholder="Buscar por nombre, apellido o cédula...">
    </div>

    <!-- Tabla -->
    <div class="table-container">
    <table border="1" class="tabla-secre">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Cédula</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-secretarias">
        <tr><td colspan="4">Cargando secretarias...</td></tr>
      </tbody>
    </table>
    </div>

    <!-- Paginación -->
    <div id="paginacion-secre" class="paginacion"></div>
  </main>

  <!-- Pie de página -->
  <footer>
    <p>© 2025 CENEAC. Todos los derechos reservados.</p>
  </footer>

  <!-- Modal Editar Secretaria -->
  <div id="modal-editar" class="modal-overlay">
    <div class="modal-content">
      <button class="close-btn" id="cerrar-modal">&times;</button>
      <h2>Editar Secretaria</h2>
      <form id="form-editar-secre">
        <input type="hidden" name="id_secre" id="edit-id-secre">
        <input type="text" name="nombre" id="edit-nombre" placeholder="Nombre" required>
        <input type="text" name="apellido" id="edit-apellido" placeholder="Apellido" required>
        <input type="text" name="telefono" id="edit-telefono" placeholder="Teléfono" required>
        <input type="email" name="correo" id="edit-correo" placeholder="Correo" required>
        <button type="submit">Guardar Cambios</button>
      </form>
    </div>
  </div>

<!-- Modal Retirar Secretaria -->
<div id="modal-retirar" class="modal-overlay">
  <div class="modal-content">
    <button class="close-btn" id="cerrar-modal-retirar">&times;</button>
    <h2>Retirar Secretaria</h2>
    <form id="form-retirar-secre">
      <input type="hidden" id="retirar-id-secre" name="id_secre">

      <label for="razon">Razón (opcional):</label>
      <textarea id="razon" name="razon" rows="3" placeholder="Escriba la razón si lo desea..."></textarea>

      <div class="modal-acciones">
        <button type="submit">Retirar</button>
        <button type="button" id="cancelar-retirar">Cancelar</button>
      </div>
    </form>
  </div>
</div>


  <!-- Modal Cambiar Estado -->
  <div id="modal-estado" class="modal-overlay">
    <div class="modal-content">
      <button class="close-btn" id="cerrar-modal-estado">&times;</button>
      <h2>Cambiar estado de la secretaria</h2>
      <form id="form-estado-secre">
        <input type="hidden" name="id_secre" id="estado-id-secre">

        <label for="estado-status">Nuevo estado:</label>
        <select name="estado" id="estado-status" required>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
          <option value="reposo">Reposo</option>
          <option value="jubilado">Jubilado</option>
          <option value="ausente">Ausente</option>
        </select>

       <label for="razon-estado">Razón / Descripción:</label>
        <textarea name="razon" id="razon-estado" placeholder="Escriba la razón..."></textarea>

        <button type="submit">Guardar cambios</button>
      </form>
    </div>
  </div>

  <!-- Modal Ver Estado Detalle -->
  <div id="modal-estado-detalle" class="modal-overlay">
    <div class="modal-content">
      <button class="close-btn" id="cerrar-modal-estado-detalle">&times;</button>
      <h2>Detalles del estado</h2>
      <p><strong>Nombre:</strong> <span id="det-nombre"></span></p>
      <p><strong>Estado:</strong> <span id="det-status"></span></p>
      <p><strong>Razón:</strong> <span id="det-razon"></span></p>
      <p><strong>Fecha modificación:</strong> <span id="det-fecha"></span></p>
    </div>
  </div>

  <!-- Modal Información de Secretaria -->
  <div id="modal-info-secretaria" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Información de la Secretaria</h2>
        <span class="close" onclick="cerrarModalInfoSecretaria()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="info-grid">
          <div class="info-item">
            <strong>Nombre:</strong>
            <span id="info-secre-nombre"></span>
          </div>
          <div class="info-item">
            <strong>Apellido:</strong>
            <span id="info-secre-apellido"></span>
          </div>
          <div class="info-item">
            <strong>Cédula:</strong>
            <span id="info-secre-cedula"></span>
          </div>
          <div class="info-item">
            <strong>Teléfono:</strong>
            <span id="info-secre-telefono"></span>
          </div>
          <div class="info-item">
            <strong>Correo:</strong>
            <span id="info-secre-correo"></span>
          </div>
          <div class="info-item">
            <strong>Fecha de Ingreso:</strong>
            <span id="info-secre-fecha"></span>
          </div>
          <div class="info-item">
            <strong>Estado:</strong>
            <span id="info-secre-estado" class="estado-badge"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-cerrar" onclick="cerrarModalInfoSecretaria()">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="secretarias.js"></script>
  <script src="script.js"></script>
  
  <!-- JavaScript del sidebar centralizado -->
  <?= renderSidebarScript() ?>
  
  <script>
// Función para mostrar información de la secretaria en modal
function mostrarInfoSecretaria(id, nombre, apellido, cedula, telefono, correo, fecha, estado) {
    document.getElementById('info-secre-nombre').textContent = nombre;
    document.getElementById('info-secre-apellido').textContent = apellido;
    document.getElementById('info-secre-cedula').textContent = cedula;
    document.getElementById('info-secre-telefono').textContent = telefono;
    document.getElementById('info-secre-correo').textContent = correo;
    document.getElementById('info-secre-fecha').textContent = fecha ? new Date(fecha).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }) : 'N/A';
    
    const estadoElement = document.getElementById('info-secre-estado');
    estadoElement.textContent = estado;
    estadoElement.className = 'estado-badge ' + estado;
    
    document.getElementById('modal-info-secretaria').style.display = 'block';
    
    // Prevenir scroll del body cuando el modal está abierto
    document.body.style.overflow = 'hidden';
}

// Función para cerrar el modal de información
function cerrarModalInfoSecretaria() {
    document.getElementById('modal-info-secretaria').style.display = 'none';
    
    // Restaurar scroll del body
    document.body.style.overflow = 'auto';
}

// Mejorar accesibilidad táctil en dispositivos móviles
document.addEventListener('DOMContentLoaded', function() {
    // Agregar clase para dispositivos táctiles
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    // Mejorar interacción con botones en móvil
    const buttons = document.querySelectorAll('button, .close-btn');
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
            // Cerrar modal de información de secretaria
            cerrarModalInfoSecretaria();
            
            // Cerrar otros modales
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                if (modal.style.display === 'block' || modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
    
    // Prevenir scroll del body cuando los modales están abiertos
    const modalCloseButtons = document.querySelectorAll('.close-btn, .close');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.body.style.overflow = 'auto';
        });
    });
    
    // Cerrar modal al hacer clic fuera de él
    window.onclick = function(event) {
        const modalInfo = document.getElementById('modal-info-secretaria');
        if (event.target == modalInfo) {
            cerrarModalInfoSecretaria();
        }
    }
});
</script>

</body>
</html>
