<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/listaprofe.php';

// Asegurar que la sesión esté iniciada antes de usar los componentes de navegación
ensureSessionStarted();

// Configurar página actual y ruta base
$currentPage = 'profesores';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Profesores - CENEAC Admin</title>
    
    <!-- Estilos del sidebar centralizado - patrón estándar -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
    
    <!-- Estilos responsive centralizados -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/admin_responsive.css" />
    
    <!-- Estilos específicos del módulo (solo elementos propios, sin navegación) -->
    <link rel="stylesheet" href="profesor_module_styles.css" />
    
    <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Botón para abrir el sidebar - estilo calendario -->
<button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>

<!-- Sidebar centralizado - patrón estándar -->
<?= renderAdminSidebar($currentPage, $basePath) ?>
    
    <section class="contenedor-principal">
        <main class="contenido">
            <h1>Gestión de Profesores</h1>

            <?php if ($success): ?>
                <div class="alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="form-profe" method="post" action="#">
                <input type="text" name="nombre" placeholder="Nombre" required />
                <input type="text" name="apellido" placeholder="Apellido" required />
                <input type="text" name="cedula" placeholder="Cédula" required />
                <input type="text" name="telefono" placeholder="Teléfono" required />
                <input type="email" name="correo" placeholder="Correo" required />
                <input type="text" name="especialidad" placeholder="Especialidad" required style="text-transform: uppercase;" />
                <button type="submit">¡Añadir Profesor!</button>
                <button type="button" onclick="window.location.href='historial_ausencias.php'" class="btn-historial">Historial</button>
            </form>

            <div class="table-container">
                <table class="tabla-profesor" border="1">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Cédula</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-profesores">
                    <?php 
                    
                    if (!empty($profesores)) {
                       
                        foreach ($profesores as $p) {
                            ?>
                            <tr data-id="<?= htmlspecialchars($p['id_profe'] ?? '') ?>">
                                <td><?= htmlspecialchars(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($p['cedula'] ?? '') ?></td>
                                <td><?= htmlspecialchars($p['especialidad'] ?? '') ?></td>
                                <td>
                                    <span class="estado-badge <?= htmlspecialchars($p['status'] ?? '') ?>">
                                        <?= htmlspecialchars($p['status'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <button class="btn-info" onclick="mostrarInfoProfesor(<?= htmlspecialchars($p['id_profe'] ?? '') ?>, '<?= htmlspecialchars($p['nombre'] ?? '') ?>', '<?= htmlspecialchars($p['apellido'] ?? '') ?>', '<?= htmlspecialchars($p['cedula'] ?? '') ?>', '<?= htmlspecialchars($p['telefono'] ?? '') ?>', '<?= htmlspecialchars($p['correo'] ?? '') ?>', '<?= htmlspecialchars($p['especialidad'] ?? '') ?>', '<?= isset($p['fecha_registro']) ? date('d/m/Y H:i', strtotime($p['fecha_registro'])) : 'N/A' ?>', '<?= htmlspecialchars($p['status'] ?? '') ?>')">
                                            Info
                                        </button>
                                        
                                        <?php if (($p['status'] ?? '') == 'activo') { ?>
                                            <button class="btn-ausentar" 
                                                    onclick="window.location.href='ausente_profe.php?id=<?= htmlspecialchars($p['id_profe'] ?? '') ?>&nombre=<?= urlencode(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? '')) ?>'">
                                                Ausentar
                                            </button>
                                        <?php } elseif (($p['status'] ?? '') == 'ausente') { ?>
                                            <button class="btn-reactivar" data-id="<?= htmlspecialchars($p['id_profe'] ?? '') ?>">Reactivar</button>
                                        <?php } ?>
                                        
                                        <!-- Botón de eliminar -->
                                        <form method="post" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este profesor? Esta acción no se puede deshacer.')">
                                            <input type="hidden" name="id_profe" value="<?= htmlspecialchars($p['id_profe'] ?? '') ?>">
                                            <button type="submit" name="eliminar_profe" class="btn-eliminar">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        // Si no hay profes, muestro un mensaje amigable.
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">
                                ¡Vaya! No encontramos ningún profesor registrado.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </main>
    </section>

    <!-- Modal para mostrar información del profesor -->
     
    <div id="modalInfoProfesor" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Información del Profesor</h2>
                <span class="close" onclick="cerrarModalInfo()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Nombre:</strong>
                        <span id="info-nombre"></span>
                    </div>
                    <div class="info-item">
                        <strong>Apellido:</strong>
                        <span id="info-apellido"></span>
                    </div>
                    <div class="info-item">
                        <strong>Cédula:</strong>
                        <span id="info-cedula"></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono:</strong>
                        <span id="info-telefono"></span>
                    </div>
                    <div class="info-item">
                        <strong>Correo:</strong>
                        <span id="info-correo"></span>
                    </div>
                    <div class="info-item">
                        <strong>Especialidad:</strong>
                        <span id="info-especialidad"></span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha de Ingreso:</strong>
                        <span id="info-fecha"></span>
                    </div>
                    <div class="info-item">
                        <strong>Estado:</strong>
                        <span id="info-estado" class="estado-badge"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cerrar" onclick="cerrarModalInfo()">Cerrar</button>
            </div>
        </div>
    </div>
    
    <footer>
        <p>© 2025 CENEAC. Todos los derechos reservados.</p>
    </footer>
    <script src="lista.js"></script>
    <script src="script.js"></script>
    
    <!-- JavaScript del sidebar centralizado -->
    <?= renderSidebarScript() ?>
    
    <script>
    // Función para mostrar información del profesor en modal
    function mostrarInfoProfesor(id, nombre, apellido, cedula, telefono, correo, especialidad, fecha, estado) {
        document.getElementById('info-nombre').textContent = nombre;
        document.getElementById('info-apellido').textContent = apellido;
        document.getElementById('info-cedula').textContent = cedula;
        document.getElementById('info-telefono').textContent = telefono;
        document.getElementById('info-correo').textContent = correo;
        document.getElementById('info-especialidad').textContent = especialidad;
        document.getElementById('info-fecha').textContent = fecha;
        
        const estadoElement = document.getElementById('info-estado');
        estadoElement.textContent = estado;
        estadoElement.className = 'estado-badge ' + estado;
        
        document.getElementById('modalInfoProfesor').style.display = 'block';
        
        // Prevenir scroll del body cuando el modal está abierto
        document.body.style.overflow = 'hidden';
    }

    // Función para cerrar el modal
    function cerrarModalInfo() {
        document.getElementById('modalInfoProfesor').style.display = 'none';
        
        // Restaurar scroll del body
        document.body.style.overflow = 'auto';
    }

    // Cerrar modal al hacer clic fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById('modalInfoProfesor');
        if (event.target == modal) {
            cerrarModalInfo();
        }
    }
    
    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            cerrarModalInfo();
        }
    });
    
    // Mejorar accesibilidad táctil en dispositivos móviles
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar clase para dispositivos táctiles
        if ('ontouchstart' in window) {
            document.body.classList.add('touch-device');
        }
        
        // Mejorar interacción con botones en móvil
        const buttons = document.querySelectorAll('button, .btn-info, .btn-ausentar, .btn-reactivar, .btn-eliminar');
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