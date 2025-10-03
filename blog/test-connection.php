<?php
/**
 * Archivo de prueba para verificar la conexión del blog
 */

echo "<h2>Prueba de Conexión - Blog Mizton</h2>";
echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";

// Verificar rutas de archivos
echo "<h3>Verificación de Archivos:</h3>";
$files_to_check = [
    'config/blog-config.php',
    'config/database-blog.php',
    '../database.php',
    '../../database.php',
    '../bootstrap-landing.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p>✅ {$file} - EXISTE</p>";
    } else {
        echo "<p>❌ {$file} - NO EXISTE (buscado en: {$full_path})</p>";
    }
}

try {
    echo "<h3>Cargando Configuración:</h3>";
    
    // Incluir configuración
    require_once 'config/blog-config.php';
    
    echo "<p>✅ Configuración cargada correctamente</p>";
    
    // Probar conexión a BD
    $db = getBlogDB();
    echo "<p>✅ Conexión a base de datos establecida</p>";
    
    // Mostrar información de la BD
    $stmt = $db->query("SELECT DATABASE() as db_name");
    $db_info = $stmt->fetch();
    echo "<p>📊 Base de datos actual: " . $db_info['db_name'] . "</p>";
    
    // Verificar si las tablas existen
    $stmt = $db->query("SHOW TABLES LIKE 'blog_posts'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabla blog_posts existe</p>";
        
        // Contar posts existentes
        $stmt = $db->query("SELECT COUNT(*) FROM blog_posts");
        $count = $stmt->fetchColumn();
        echo "<p>📊 Posts en base de datos: {$count}</p>";
    } else {
        echo "<p>⚠️ Tabla blog_posts no existe, creando...</p>";
        createBlogTables();
        echo "<p>✅ Tablas creadas</p>";
    }
    
    // Verificar tabla newsletter
    $stmt = $db->query("SHOW TABLES LIKE 'blog_newsletter'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabla blog_newsletter existe</p>";
        
        $stmt = $db->query("SELECT COUNT(*) FROM blog_newsletter");
        $subscribers = $stmt->fetchColumn();
        echo "<p>📧 Suscriptores: {$subscribers}</p>";
    }
    
    echo "<h3>🎉 Todo funciona correctamente!</h3>";
    echo "<p><a href='index.php'>← Volver al Blog</a> | <a href='admin/'>Panel Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error Detectado:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
