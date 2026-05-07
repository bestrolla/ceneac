# Sistema de Navegación Centralizado - CENEAC

Este sistema permite gestionar la navegación de forma centralizada, evitando duplicar código en cada página del panel administrativo y de secretarias.

## Estructura de Archivos

```
components/navigation/
├── admin_sidebar.php         # Componente principal del sidebar para administradores
├── secretaria_sidebar.php    # Componente principal del sidebar para secretarias
├── sidebar_styles.css        # Estilos CSS del sidebar (compartido)
├── example_usage.php         # Ejemplo de implementación
└── README.md                # Esta documentación
```

## ✅ Estado de Implementación

### Páginas Administrativas Migradas:
- ✅ `admin/inicio/vista/inicio.php`
- ✅ `admin/Calendario/vista/index.php`
- ✅ `admin/agregar_cursos/vista/lista_cursos.php`
- ✅ `admin/agregar_salon/vista/lista_salon.php`
- ✅ `admin/agregar_profe/vista/lista_profe.php`
- ✅ `admin/agregar_profe/vista/ausente_profe.php`
- ✅ `admin/agregar_profe/vista/historial_ausencias.php`
- ✅ `admin/agregar_secre/vista/lista_secretaria.php`
- ✅ `admin/agregar_secre/vista/lista_secretarias_retiradas.php`
- ✅ `admin/configuracion/vista/configuracion.php`

### Páginas de Secretaria Migradas:
- ✅ `secretaria/lobby/vista/Lobby.php`
- ✅ `secretaria/cursos/vista/cursos.php`
- ✅ `secretaria/espera/vista/espera.php`
- ✅ `secretaria/configuracion/vista/configuracion.php`

**Total: 14 páginas migradas al sistema centralizado**

## Cómo Usar

### 1. Incluir en tu página PHP

```php
<?php 
// Incluir el componente de navegación centralizado
require_once '../../../components/navigation/admin_sidebar.php';

// Configurar página actual y ruta base
$currentPage = 'secretarias'; // Ver opciones disponibles abajo
$basePath = '../../../';      // Ajustar según profundidad de carpetas
?>
```

### 2. Estructura HTML básica

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Página - CENEAC Admin</title>
    
    <!-- IMPORTANTE: Incluir estilos del sidebar -->
    <link rel="stylesheet" href="<?= $basePath ?>components/navigation/sidebar_styles.css" />
    
    <!-- Tus estilos específicos -->
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <!-- Barra superior centralizada -->
    <?= renderAdminTopBar('Título de tu Página', $basePath) ?>

    <!-- Sidebar centralizado -->
    <?= renderAdminSidebar($currentPage, $basePath) ?>

    <!-- Tu contenido aquí -->
    <main class="contenido">
        <h1>Contenido de tu página</h1>
        <!-- ... resto del contenido ... -->
    </main>

    <!-- Tus scripts específicos -->
    <script src="tu-script.js"></script>
    
    <!-- IMPORTANTE: JavaScript del sidebar al final -->
    <?= renderSidebarScript() ?>
</body>
</html>
```

## Opciones de Página Actual

### Para Administradores (`admin_sidebar.php`):
- `'inicio'` - Página de inicio
- `'secretarias'` - Lista de secretarias
- `'secretarias_retiradas'` - Historial de secretarias retiradas
- `'profesores'` - Lista de profesores
- `'cursos'` - Gestión de cursos
- `'salon'` - Gestión de salones
- `'calendario'` - Calendario
- `'lista_espera'` - Lista de espera
- `'configuracion'` - Configuración

### Para Secretarias (`secretaria_sidebar.php`):
- `'lobby'` - Página de inicio/lobby
- `'cursos'` - Gestión de cursos
- `'espera'` - Lista de espera
- `'configuracion'` - Configuración

## Configuración de Rutas Base

El `$basePath` debe apuntar desde tu archivo actual hasta la raíz del proyecto:

```php
// Para archivos en admin/modulo/vista/
$basePath = '../../../';

// Para archivos en admin/vista/
$basePath = '../../';

// Para archivos en raíz/
$basePath = './';
```

## Funciones Disponibles

### `renderAdminSidebar($currentPage, $basePath)`
Genera el HTML del sidebar con navegación completa.

### `renderAdminTopBar($title, $basePath)`
Genera la barra superior con logo y título.

### `renderSidebarScript()`
Genera el JavaScript necesario para el funcionamiento del sidebar.

## Características

- **Responsive**: Se adapta automáticamente a dispositivos móviles
- **Indicador de página activa**: Resalta la página actual
- **Información del usuario**: Muestra el usuario logueado
- **Iconos visuales**: Cada opción tiene su icono representativo
- **Animaciones suaves**: Transiciones CSS para mejor UX
- **Accesibilidad**: Navegación por teclado y lectores de pantalla

## Personalización

### Agregar nuevos elementos al menú

Edita el array `$menuItems` en `admin_sidebar.php`:

```php
$menuItems = [
    'nueva_opcion' => [
        'title' => 'Nueva Opción',
        'url' => $basePath . 'nueva/vista/nueva.php',
        'icon' => '🆕'
    ],
    // ... resto de opciones
];
```

### Modificar estilos

Edita `sidebar_styles.css` o agrega tus propios estilos CSS.

## Migración de Páginas Existentes

1. **Eliminar** el HTML del menú existente
2. **Incluir** el componente centralizado
3. **Configurar** `$currentPage` y `$basePath`
4. **Agregar** los estilos CSS del sidebar
5. **Incluir** el JavaScript al final

## Ejemplo Completo

Ver `example_usage.php` para un ejemplo completo de implementación.

## Beneficios

- **Centralización**: Un solo lugar para mantener la navegación
- **Consistencia**: Todas las páginas tienen el mismo aspecto y comportamiento
- **Mantenimiento**: Cambios en un solo archivo se reflejan en todas las páginas
- **Responsive**: Diseño adaptativo para móviles y escritorio
- **Accesibilidad**: Navegación por teclado y estructura semántica
- **Performance**: CSS y JavaScript optimizados
- **Escalabilidad**: Fácil agregar nuevas páginas al sistema

## Migración Completada

✅ **14 páginas migradas exitosamente** al sistema de navegación centralizado:
- 10 páginas administrativas
- 4 páginas de secretaria

El sistema está completamente funcional y todas las páginas principales del proyecto CENEAC ahora utilizan la navegación centralizada, eliminando la duplicación de código y mejorando la mantenibilidad del proyecto.
