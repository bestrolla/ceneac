<?php
/**
 * Componente de navegación lateral para secretarias
 * Sistema centralizado para evitar duplicación de código
 */

// Verificar que el usuario tenga acceso de secretaria
require_once __DIR__ . '/../../verificacion/verificar_acceso.php';
verificarAcceso('secretaria');

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
        'username' => $_SESSION['usuario'] ?? 'Secretaria'
    ];
}

/**
 * Genera el menú lateral de la secretaria
 * @param string $currentPage Página actual para marcar como activa
 * @param string $basePath Ruta base desde donde se incluye (ej: '../../')
 */
function renderSecretariaSidebar($currentPage = '', $basePath = '../../') {
    // Configuración de menú para secretarias
    $menuItems = [
        'lobby' => [
            'title' => 'Inicio',
            'url' => $basePath . 'secretaria/lobby/vista/Lobby.php',
            'icon' => '🏠'
        ],
        'cursos' => [
            'title' => 'Gestión de Cursos',
            'url' => $basePath . 'secretaria/cursos/vista/cursos.php',
            'icon' => '📚'
        ],
        'espera' => [
            'title' => 'Lista de Espera',
            'url' => $basePath . 'secretaria/espera/vista/espera.php',
            'icon' => '⏳'
        ],
        'configuracion' => [
            'title' => 'Configuración',
            'url' => $basePath . 'secretaria/configuracion/vista/configuracion.php',
            'icon' => '⚙️'
        ]
    ];
    
    // Calcular ruta para cerrar sesión
    $logoutPath = str_repeat('../', substr_count($basePath, '../')) . 'verificacion/cerrar_sesion.php';
    
    ob_start();
    ?>
    <!-- Menú lateral - diseño estándar -->
    <div id="sidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="toggleSidebar()">&times;</a>
        
        <!-- Información del usuario -->
        <div class="user-info">
            <div class="user-avatar">👩‍💼</div>
            <div class="user-details">
                <?php 
                $currentUser = getCurrentUser();
                if ($currentUser): 
                ?>
                    <span class="username"><?= htmlspecialchars($currentUser['username']) ?></span>
                    <span class="user-role">Secretaria</span>
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
            
            <!-- Separador -->
            <div class="nav-separator"></div>
            
            <!-- Cerrar sesión -->
            <a href="<?= htmlspecialchars($logoutPath) ?>" class="nav-item logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar sesión</span>
            </a>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Genera la barra superior para secretarias
 * @param string $title Título de la página
 * @param string $basePath Ruta base desde donde se incluye
 * @param bool $useCustomButton Si usar el botón personalizado estilo calendario (por defecto true)
 */
function renderSecretariaTopBar($title = 'CENEAC Secretaria', $basePath = '../../', $useCustomButton = true) {
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
            <img src="<?= $basePath ?>img/logo.gif" alt="Logo CENEAC" class="logo" />
            <h1><?= htmlspecialchars($title) ?></h1>
        </div>
        <div class="top-bar-actions">
            <?php 
            $currentUser = getCurrentUser();
            if ($currentUser): 
            ?>
                <span class="welcome-text">Bienvenida, <?= htmlspecialchars($currentUser['username']) ?></span>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- CSS para la barra superior -->
    <style>
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background: #3478F5;
            border-bottom: 1px solid #2a6ed0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            height: 40px;
            width: auto;
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
 * Genera el JavaScript necesario para el sidebar (reutiliza el mismo del admin)
 */
function renderSecretariaSidebarScript() {
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
