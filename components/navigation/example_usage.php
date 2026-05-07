<?php
/**
 * EJEMPLO DE USO DEL SISTEMA DE NAVEGACIÓN CENTRALIZADO
 * 
 * Copia este código en cualquier página de administrador para usar el menú centralizado
 */

// 1. Incluir el componente de navegación
require_once __DIR__ . '/../../components/navigation/admin_sidebar.php';

// 2. Definir la página actual y ruta base
$currentPage = 'inicio'; // Cambiar según la página: 'secretarias', 'profesores', etc.
$basePath = '../../'; // Ajustar según la profundidad de carpetas

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título de la Página - CENEAC Admin</title>
    
    <!-- Incluir estilos del sidebar -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css">
    
    <!-- Tus estilos adicionales aquí -->
    <style>
        /* Estilos específicos de tu página */
        .main-content {
            padding: 20px;
            transition: margin-left 0.3s;
        }
    </style>
</head>
<body>
    <!-- Barra superior -->
    <?= renderAdminTopBar('Título de tu Página', $basePath) ?>
    
    <!-- Sidebar -->
    <?= renderAdminSidebar($currentPage, $basePath) ?>
    
    <!-- Contenido principal -->
    <div class="main-content">
        <h1>Contenido de tu página aquí</h1>
        <p>Todo tu contenido va aquí...</p>
    </div>
    
    <!-- JavaScript del sidebar -->
    <?= renderSidebarScript() ?>
</body>
</html>
