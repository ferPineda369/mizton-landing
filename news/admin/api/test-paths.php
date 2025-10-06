<?php
/**
 * Verificar rutas de archivos API
 */

echo "<h2>🔍 Verificación de Rutas API</h2>";

echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Directorio padre:</strong> " . dirname(__DIR__) . "</p>";
echo "<p><strong>Directorio blog:</strong> " . dirname(dirname(__DIR__)) . "</p>";

echo "<h3>📁 Archivos a verificar:</h3>";

$files_to_check = [
    'config/blog-config.php' => dirname(__DIR__) . '/../config/blog-config.php',
    'includes/blog-functions.php' => dirname(__DIR__) . '/../includes/blog-functions.php',
    'includes/admin-functions.php' => dirname(__DIR__) . '/../includes/admin-functions.php'
];

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        echo "<p>✅ <strong>{$name}</strong> - EXISTE en: {$path}</p>";
    } else {
        echo "<p>❌ <strong>{$name}</strong> - NO EXISTE en: {$path}</p>";
        
        // Intentar rutas alternativas
        $alt_paths = [
            '../../config/blog-config.php',
            '../config/blog-config.php',
            dirname(dirname(dirname(__FILE__))) . '/config/blog-config.php'
        ];
        
        foreach ($alt_paths as $alt_path) {
            if (file_exists($alt_path)) {
                echo "<p>   ➡️ ENCONTRADO en: {$alt_path}</p>";
                break;
            }
        }
    }
}

// Probar carga de configuración
echo "<h3>🧪 Prueba de Carga:</h3>";
try {
    $config_path = dirname(__DIR__) . '/../config/blog-config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
        echo "<p>✅ <strong>Configuración cargada exitosamente</strong></p>";
        
        if (function_exists('getBlogDB')) {
            echo "<p>✅ <strong>Función getBlogDB disponible</strong></p>";
            
            $db = getBlogDB();
            if ($db) {
                echo "<p>✅ <strong>Conexión a BD establecida</strong></p>";
            } else {
                echo "<p>❌ <strong>Error en conexión a BD</strong></p>";
            }
        } else {
            echo "<p>❌ <strong>Función getBlogDB no disponible</strong></p>";
        }
    } else {
        echo "<p>❌ <strong>No se pudo cargar la configuración</strong></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
