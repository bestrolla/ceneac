<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/ausente.php';

// Configurar página actual y ruta base
$currentPage = 'profesores';
$basePath = '../../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencia - CENEAC Admin</title>
    
    <!-- Estilos del sidebar centralizado -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
    
    <!-- Estilos específicos de la página -->
    <link rel="stylesheet" href="styles_ausente.css">
    <link rel="shortcut icon" href="/proto/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Registrar Ausencia', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>

    <div class="container">
        <?php if (isset($error) && !empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <h1>Registrar Ausencia del Profesor</h1>
        
        <?php if ($id_profe): ?>
            <div class="profesor-info">
                <h3>Profesor: <?= htmlspecialchars($nombre_profe) ?></h3>
                <p>ID: <?= htmlspecialchars($id_profe) ?></p>
                <?php if (isset($profesor['status']) && $profesor['status'] === 'ausente'): ?>
                    <p><strong>Estado actual: Ausente</strong></p>
                    <form method="post" action="reactivar_profesor.php" style="display:inline;">
                        <input type="hidden" name="id_profe" value="<?= $id_profe ?>">
                      
                    </form>
                <?php endif; ?>
            </div>

            <form method="post">
                <input type="hidden" name="id_profe" value="<?= htmlspecialchars($id_profe) ?>">
                <input type="hidden" name="nombre_profe" value="<?= htmlspecialchars($nombre_profe) ?>">

                <div class="form-group">
                    <label for="tipo_ausencia">Tipo de Ausencia:</label>
                    <select id="tipo_ausencia" name="tipo_ausencia" required>
                        <option value="">Selecciona el motivo...</option>
                        <option value="reposo">Reposo Médico</option>
                        <option value="jubilacion">Jubilación</option>
                        <option value="despido">Despido</option>
                        <option value="renuncia">Renuncia</option>
                        <option value="otra">Otra</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="detalles">Detalles:</label>
                    <textarea id="detalles" name="detalles" placeholder="Detalles de la ausencia..." required></textarea>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn-cancelar" onclick="window.location.href='lista_profe.php'">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar Ausencia</button>
                </div>
            </form>

            <!-- Historial completo de ausencias -->
            <div class="ausencias-container">
                <h3 class="historial-title">Historial Completo de Ausencias</h3>
                
                <?php if (isset($error_ausencias) && !empty($error_ausencias)): ?>
                    <div class="error"><?= htmlspecialchars($error_ausencias) ?></div>
                <?php endif; ?>
                
                <div class="ausencias-list">
                    <?php if (empty($historial_ausencias)): ?>
                        <p>No hay historial de ausencias registradas.</p>
                    <?php else: ?>
                        <?php foreach ($historial_ausencias as $registro): ?>
                            <div class="ausencia-item">
                                <div class="ausencia-registro"><?= htmlspecialchars($registro['registro']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="error">No se ha especificado un profesor válido</div>
            <div class="info">
                <p>Por favor, seleccione un profesor desde la <a href="lista_profe.php">lista de profesores</a> para registrar una ausencia.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>© 2025 CENEAC. Todos los derechos reservados.</p>
    </footer>
    
    <!-- JavaScript del sidebar centralizado -->
    <?= renderSidebarScript() ?>
    
</body>
</html>