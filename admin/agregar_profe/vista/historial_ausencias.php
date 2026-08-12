<?php
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';
require_once '../logica/tabla_ausente.php';

// Configurar página actual y ruta base
$currentPage = 'profesores';
$basePath = '../../../';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Historial de Ausencias - CENEAC Admin</title>
    
    <!-- Estilos del sidebar centralizado -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
    
    <!-- Estilos específicos de la página -->
    <link rel="stylesheet" href="styles_profes.css" />
    <link rel="shortcut icon" href="/login/login/vista/img/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Barra superior centralizada -->
<?= renderAdminTopBar('Historial de Ausencias', $basePath) ?>

<!-- Sidebar centralizado -->
<?= renderAdminSidebar($currentPage, $basePath) ?>
    
    <section class="contenedor">
        <main class="contenido">
            <h1>Historial de Ausencias de Profesores</h1>

            <button onclick="window.location.href='lista_profe.php'" class="back-button">← Regresar</button>
           
            <!-- Tabla de resultados -->
            <table class="tabla-profesor" border="1">
                <thead>
                    <tr>
                        <th class="sort-header">
                            <a href="<?= OrdenadorAusencias::generarUrlOrdenamiento('id_profe', $orden, $direccion) ?>" class="sort-link">
                                ID Profesor <?= OrdenadorAusencias::obtenerIconoOrdenamiento($orden, 'id_profe', $direccion) ?>
                            </a>
                        </th>
                        <th class="sort-header">
                            Profesor 
                        </th>
                        <th class="sort-header">
                            <a href="<?= OrdenadorAusencias::generarUrlOrdenamiento('fecha_inicio_ausencia', $orden, $direccion) ?>" class="sort-link">
                                Fecha Inicio <?= OrdenadorAusencias::obtenerIconoOrdenamiento($orden, 'fecha_inicio_ausencia', $direccion) ?>
                            </a>
                        </th>
                        <th class="sort-header">
                            Razón 
                        </th>
                        <th>Detalles</th>
                        <th class="sort-header">
                            <a href="<?= OrdenadorAusencias::generarUrlOrdenamiento('fecha_registro', $orden, $direccion) ?>" class="sort-link">
                                Fecha Registro <?= OrdenadorAusencias::obtenerIconoOrdenamiento($orden, 'fecha_registro', $direccion) ?>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ausenciasOrdenadas)): ?>
                        <tr>
                            <td colspan="6" class="no-results">No se encontraron registros de ausencias</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ausenciasOrdenadas as $ausencia): ?>
                            <tr>
                                <td><?= htmlspecialchars($ausencia['id_profe'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(($ausencia['nombre'] ?? '') . ' ' . ($ausencia['apellido'] ?? '')) ?></td>
                                <td><?= !empty($ausencia['fecha_inicio_ausencia']) ? date('d/m/Y', strtotime($ausencia['fecha_inicio_ausencia'])) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($ausencia['razon_texto'] ?? OrdenadorAusencias::obtenerTextoRazon($ausencia['razon'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($ausencia['detalles'] ?? 'N/A') ?></td>
                                <td><?= !empty($ausencia['fecha_registro']) ? date('d/m/Y H:i', strtotime($ausencia['fecha_registro'])) : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </section>
    
    <footer>
        <p>© 2025 CENEAC. Todos los derechos reservados.</p>
    </footer>

    <script src="script.js"></script>
    
    <!-- JavaScript del sidebar centralizado -->
    <?= renderSidebarScript() ?>
    
</body>
</html>