<?php
/**
 * Componente de navegación lateral para administradores
 * Sistema centralizado para evitar duplicación de código
 */

// Verificar que el usuario tenga acceso de administrador
// require_once __DIR__ . '/../../verificacion/verificar_acceso.php';
// verificarAcceso('administrador');

/**
 * Inicializa la sesión si no está ya iniciada
 * Esta función debe ser llamada antes de cualquier función que necesite acceso a la sesión
 */
function ensureSessionStarted() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Obtiene los datos del usuario actual de forma segura
 * @return array Datos del usuario actual
 */
function getCurrentUser() {
    ensureSessionStarted();
    return [
        'username' => $_SESSION['usuario'] ?? 'Admin'
    ];
}

/**
 * Genera el menú lateral del administrador
 * @param string $currentPage Página actual para marcar como activa
 * @param string $basePath Ruta base desde donde se incluye (ej: '../../')
 */
function renderAdminSidebar($currentPage = '', $basePath = '../../') {
    // Configuración de menú
    $menuItems = [
        'inicio' => [
            'title' => 'Inicio',
            'url' => $basePath . 'admin/inicio/vista/inicio.php',
            'icon' => '🏠'
        ],
        'secretarias' => [
            'title' => 'Gestión de Secretarias',
            'url' => $basePath . 'admin/agregar_secre/vista/lista_secretaria.php',
            'icon' => '👩‍💼'
        ],
        'secretarias_retiradas' => [
            'title' => 'Secretarias Retiradas',
            'url' => $basePath . 'admin/agregar_secre/vista/lista_secretarias_retiradas.php',
            'icon' => '📋'
        ],
        'profesores' => [
            'title' => 'Gestión de Profesores',
            'url' => $basePath . 'admin/agregar_profe/vista/lista_profe.php',
            'icon' => '👨‍🏫'
        ],
        'cursos' => [
            'title' => 'Gestión de Cursos',
            'url' => $basePath . 'admin/agregar_cursos/vista/lista_cursos.php',
            'icon' => '📚'
        ],
        'salon' => [
            'title' => 'Gestión de Salones',
            'url' => $basePath . 'admin/agregar_salon/vista/lista_salon.php',
            'icon' => '🏫'
        ],
        'calendario' => [
            'title' => 'Calendario',
            'url' => $basePath . 'admin/Calendario/vista/calendario_principal.php',
            'icon' => '📅'
        ],
        'configuracion' => [
            'title' => 'Configuración',
            'url' => $basePath . 'admin/configuracion/vista/configuracion.php',
            'icon' => '⚙️'
        ]
    ];
    
    // Calcular ruta para cerrar sesión
    $logoutPath = str_repeat('../', substr_count($basePath, '../')) . 'verificacion/cerrar_sesion.php';
    
    ob_start();
    ?>
    <!-- Menú lateral con diseño estándar -->
    <div id="sidebar" class="sidebar">
        <!-- <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">&times;</a> -->
        
        <!-- Información del usuario -->
         <br>
         <br>   
        <div class="user-info">
            <div class="user-avatar">👤</div>
            <div class="user-details">
                <?php 
                $currentUser = getCurrentUser();
                if ($currentUser): 
                ?>
                    <span class="username"><?= htmlspecialchars($currentUser['username']) ?></span>
                    <span class="user-role">Administrador</span>
                <?php else: ?>
                    <span class="username">Usuario</span>
                    <span class="user-role">Administrador</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Menú de navegación -->
        <nav class="sidebar-nav">
            <?php foreach ($menuItems as $key => $item): ?>
                <a href="<?= htmlspecialchars($item['url']) ?>" 
                   class="nav-item <?= ($currentPage === $key) ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $item['icon'] ?></span>
                    <span class="nav-text"><?= htmlspecialchars($item['title']) ?></span>
                </a>
            <?php endforeach; ?>
            
            <!-- Cerrar sesión -->
            <a href="<?= htmlspecialchars($logoutPath) ?>" class="nav-item logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Genera la barra superior
 * @param string $title Título de la página
 * @param string $basePath Ruta base desde donde se incluye
 * @param bool $useCustomButton Si usar el botón personalizado estilo calendario (por defecto true)
 */
function renderAdminTopBar($title = 'CENEAC Admin', $basePath = '../../', $useCustomButton = true) {
    if ($useCustomButton) {
        // Usar botón personalizado estilo calendario (por defecto)
        ob_start();
        ?>
        <!-- Botón personalizado para abrir el sidebar - estilo calendario -->
        <button class="custom-menu-btn" onclick="toggleSidebar()">☰ Menú</button>
        <?php
        return ob_get_clean();
    }
    
    // Barra superior tradicional (solo si se especifica useCustomButton = false)
    ob_start();
    ?>
    <!-- Barra superior -->
    <header class="top-bar">
        <button class="openbtn" onclick="toggleSidebar()">☰ Menú</button>
        <div class="top-bar-title">
            <img src="img/logo.gif" alt="Logo CENEAC" class="logo" />
            <h1><?= htmlspecialchars($title) ?></h1>
        </div>
        <div class="top-bar-actions">
            <?php 
            $currentUser = getCurrentUser();
            if ($currentUser): 
            ?>
                <span class="welcome-text">Bienvenido, <?= htmlspecialchars($currentUser['username']) ?></span>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- CSS para la barra superior -->
    <style>
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #3478F5;
            padding: 5px 20px;
            box-shadow: 0px 4px 5px rgba(0, 0, 0, 0.3);
            z-index: 999;
            box-sizing: border-box;
        }
        
        .top-bar-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .top-bar-title h1 {
            margin: 0;
            font-size: 18px;
            color: white;
            font-weight: 500;
        }
        
        .logo {
            height: 50px;
            width: auto;
            border-radius: 8px;
        }
        
        .welcome-text {
            font-size: 14px;
            color: white;
        }
        
        .openbtn {
            background: #3478F5;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .openbtn:hover {
            background: #2a6ed0;
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .top-bar-title h1 {
                display: none;
            }
            
            .welcome-text {
                display: none;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Genera el JavaScript necesario para el sidebar
 */
function renderSidebarScript() {
    ob_start();
    ?>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                body.classList.remove('sidebar-open');
                if (overlay) overlay.classList.remove('active');
            } else {
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
                if (overlay) overlay.classList.add('active');
            }
        }
        
        // Crear overlay si no existe
        function createSidebarOverlay() {
            if (!document.getElementById('sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'sidebar-overlay';
                overlay.className = 'sidebar-overlay';
                overlay.addEventListener('click', function() {
                    toggleSidebar();
                });
                document.body.appendChild(overlay);
            }
        }
        
        // Cerrar sidebar al hacer click fuera (todas las pantallas)
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const openBtn = document.querySelector('.openbtn, .custom-menu-btn');
            const overlay = document.getElementById('sidebar-overlay');
            
            // Verificar si el sidebar está abierto y el click fue fuera del sidebar y botón
            if (sidebar && sidebar.classList.contains('open') && 
                !sidebar.contains(event.target) && 
                openBtn && !openBtn.contains(event.target)) {
                toggleSidebar();
            }
        });
        
        // Responsive behavior
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
                if (overlay) overlay.classList.remove('active');
            } else {
                sidebar.classList.remove('open');
                body.classList.remove('sidebar-open');
                if (overlay) overlay.classList.remove('active');
            }
        });
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            createSidebarOverlay();
            
            if (window.innerWidth > 768) {
                const sidebar = document.getElementById('sidebar');
                const body = document.body;
                sidebar.classList.add('open');
                body.classList.add('sidebar-open');
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
?>
