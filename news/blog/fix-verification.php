<?php
/**
 * Verificación rápida de correcciones aplicadas
 */

require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

echo "<h2>🔧 Verificación de Correcciones - Blog Mizton</h2>";

try {
    $db = getBlogDB();
    
    // Verificar estructura de la tabla
    echo "<h3>📋 Estructura de la Tabla blog_posts:</h3>";
    $stmt = $db->query("DESCRIBE blog_posts");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar posts existentes
    echo "<h3>📊 Posts Existentes:</h3>";
    $stmt = $db->query("SELECT id, title, created_at, published_at, status FROM blog_posts ORDER BY id DESC LIMIT 5");
    $posts = $stmt->fetchAll();
    
    if (empty($posts)) {
        echo "<p>⚠️ No hay posts en la base de datos.</p>";
        echo "<p><a href='setup-initial-content.php'>Crear contenido inicial</a></p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Título</th><th>Creado</th><th>Publicado</th><th>Estado</th></tr>";
        foreach ($posts as $post) {
            echo "<tr>";
            echo "<td>" . $post['id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($post['title'], 0, 50)) . "...</td>";
            echo "<td>" . $post['created_at'] . "</td>";
            echo "<td>" . ($post['published_at'] ?: 'No publicado') . "</td>";
            echo "<td>" . $post['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Probar función formatDate
    echo "<h3>🕒 Prueba de Función formatDate:</h3>";
    $testDates = [
        '2024-01-15 10:30:00',
        '2024-03-20 15:45:00',
        null,
        '',
        '0000-00-00 00:00:00',
        'fecha-invalida'
    ];
    
    foreach ($testDates as $testDate) {
        $formatted = formatDate($testDate);
        echo "<p><strong>Entrada:</strong> " . ($testDate ?: 'null') . " → <strong>Salida:</strong> " . $formatted . "</p>";
    }
    
    // Verificar posts con fechas
    if (!empty($posts)) {
        echo "<h3>📅 Verificación de Fechas en Posts:</h3>";
        foreach ($posts as $post) {
            $dateToUse = $post['published_at'] ?: $post['created_at'];
            $formatted = formatDate($dateToUse);
            echo "<p><strong>Post:</strong> " . htmlspecialchars(substr($post['title'], 0, 30)) . "... → <strong>Fecha:</strong> " . $formatted . "</p>";
        }
    }
    
    echo "<h3>✅ Verificación Completada</h3>";
    echo "<p><a href='index.php'>Ver Blog</a> | <a href='admin/'>Panel Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
