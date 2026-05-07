<?php
require_once '../logica/clases_logica.php';
require_once '../../../BBDD/BBDD.php';

echo "<h2>Prueba de cálculo de fecha fin (con duración desde BD)</h2>";

try {
    $logica = new CursoLogica();
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener cursos activos con sus duraciones
    $stmt = $conn->query("SELECT id_cursos, nombre_curso, duracion FROM cursos WHERE status = 'activo' LIMIT 3");
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cursos)) {
        echo "<p>No hay cursos activos en la base de datos para probar.</p>";
        exit;
    }
    
    foreach ($cursos as $curso) {
        echo "<h3>Curso: " . htmlspecialchars($curso['nombre_curso']) . "</h3>";
        echo "<p><strong>ID Curso:</strong> " . htmlspecialchars($curso['id_cursos']) . "</p>";
        echo "<p><strong>Duración desde BD:</strong> " . htmlspecialchars($curso['duracion']) . " días de clase</p>";
        
        // Casos de prueba con diferentes horarios
        $horarios_prueba = [
            [
                'dias' => 'Lunes,Miércoles,Viernes',
                'descripcion' => 'Lunes, Miércoles, Viernes'
            ],
            [
                'dias' => 'Martes,Jueves',
                'descripcion' => 'Martes, Jueves'
            ],
            [
                'dias' => 'Sábado',
                'descripcion' => 'Solo Sábados'
            ]
        ];
        
        foreach ($horarios_prueba as $horario) {
            echo "<h4>Horario: " . htmlspecialchars($horario['descripcion']) . "</h4>";
            
            $fecha_inicio = '2024-01-15';
            $duracion = intval($curso['duracion']);
            
            echo "<p><strong>Fecha inicio:</strong> " . htmlspecialchars($fecha_inicio) . "</p>";
            echo "<p><strong>Días de clase:</strong> " . htmlspecialchars($horario['dias']) . "</p>";
            
            // Calcular fecha fin usando reflexión para acceder al método privado
            $reflection = new ReflectionClass($logica);
            $method = $reflection->getMethod('calcularFechaFin');
            $method->setAccessible(true);
            
            try {
                $fecha_fin = $method->invoke($logica, $fecha_inicio, $duracion, $horario['dias']);
                
                echo "<p><strong>Fecha fin calculada:</strong> " . htmlspecialchars($fecha_fin) . "</p>";
                
                // Verificar que la fecha fin es válida
                if ($fecha_fin === '1970-01-01' || $fecha_fin === false) {
                    echo "<p style='color: red;'>❌ ERROR: Fecha fin inválida</p>";
                } else {
                    echo "<p style='color: green;'>✅ Fecha fin válida</p>";
                    
                    // Calcular días totales entre inicio y fin
                    $inicio = new DateTime($fecha_inicio);
                    $fin = new DateTime($fecha_fin);
                    $diferencia = $inicio->diff($fin);
                    echo "<p><strong>Días totales:</strong> " . $diferencia->days . " días</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error en cálculo: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            echo "<hr>";
        }
        
        echo "<br><br>";
    }
    
    // Probar con datos inválidos
    echo "<h3>Prueba con datos inválidos</h3>";
    
    $casos_invalidos = [
        [
            'fecha_inicio' => '',
            'duracion' => 10,
            'dias_clase' => 'Lunes,Miércoles,Viernes',
            'descripcion' => 'Fecha inicio vacía'
        ],
        [
            'fecha_inicio' => '2024-01-15',
            'duracion' => 0,
            'dias_clase' => 'Lunes,Miércoles,Viernes',
            'descripcion' => 'Duración cero'
        ],
        [
            'fecha_inicio' => '2024-01-15',
            'duracion' => -5,
            'dias_clase' => 'Lunes,Miércoles,Viernes',
            'descripcion' => 'Duración negativa'
        ]
    ];
    
    foreach ($casos_invalidos as $caso) {
        echo "<h4>" . htmlspecialchars($caso['descripcion']) . "</h4>";
        
        try {
            $reflection = new ReflectionClass($logica);
            $method = $reflection->getMethod('calcularFechaFin');
            $method->setAccessible(true);
            
            $fecha_fin = $method->invoke($logica, $caso['fecha_inicio'], $caso['duracion'], $caso['dias_clase']);
            
            echo "<p><strong>Resultado:</strong> " . htmlspecialchars($fecha_fin) . "</p>";
            
            if ($fecha_fin === '1970-01-01' || $fecha_fin === false) {
                echo "<p style='color: orange;'>⚠️ Fecha inválida (esperado para datos incorrectos)</p>";
            } else {
                echo "<p style='color: red;'>❌ ERROR: Debería haber fallado</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: green;'>✅ Correcto: Se capturó el error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
