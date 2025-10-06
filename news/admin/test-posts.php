<?php
/**
 * Prueba específica para verificar posts en admin
 */

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';

echo "<h2>🔍 Prueba de Posts para Admin</h2>";

try {
    $db = getBlogDB();
    
    // Verificar posts directamente en BD
    echo "<h3>📊 Posts en Base de Datos:</h3>";
    $stmt = $db->query("SELECT id, title, status, created_at, published_at FROM blog_posts ORDER BY id DESC");
    $allPosts = $stmt->fetchAll();
    
    if (empty($allPosts)) {
        echo "<p>❌ No hay posts en la base de datos.</p>";
        echo "<p><a href='../setup-initial-content.php'>Crear contenido inicial</a></p>";
    } else {
        echo "<p>✅ Total de posts: " . count($allPosts) . "</p>";
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr><th>ID</th><th>Título</th><th>Estado</th><th>Creado</th><th>Publicado</th></tr>";
        foreach ($allPosts as $post) {
            echo "<tr>";
            echo "<td>" . $post['id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($post['title'], 0, 50)) . "...</td>";
            echo "<td><strong>" . $post['status'] . "</strong></td>";
            echo "<td>" . $post['created_at'] . "</td>";
            echo "<td>" . ($post['published_at'] ?: 'No publicado') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Probar función getBlogPosts con diferentes parámetros
    echo "<h3>🧪 Prueba de función getBlogPosts():</h3>";
    
    $testCases = [
        ['status' => 'published', 'description' => 'Solo publicados'],
        ['status' => 'draft', 'description' => 'Solo borradores'],
        ['status' => 'all', 'description' => 'Todos los posts (para admin)']
    ];
    
    foreach ($testCases as $test) {
        echo "<h4>📋 " . $test['description'] . ":</h4>";
        $posts = getBlogPosts(50, 0, null, $test['status']);
        
        if (empty($posts)) {
            echo "<p>⚠️ No se encontraron posts con status: " . $test['status'] . "</p>";
        } else {
            echo "<p>✅ Encontrados: " . count($posts) . " posts</p>";
            echo "<ul>";
            foreach ($posts as $post) {
                echo "<li><strong>" . htmlspecialchars($post['title']) . "</strong> (Estado: " . $post['status'] . ")</li>";
            }
            echo "</ul>";
        }
    }
    
    // Verificar función getCategoriesWithCount
    echo "<h3>📂 Categorías con Conteo:</h3>";
    try {
        $categories = getCategoriesWithCount();
        if (empty($categories)) {
            echo "<p>⚠️ No se encontraron categorías.</p>";
        } else {
            echo "<ul>";
            foreach ($categories as $category => $count) {
                echo "<li><strong>" . $category . "</strong>: " . $count . " posts</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error en getCategoriesWithCount: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>🎯 Conclusión:</h3>";
    if (!empty($allPosts)) {
        $publishedCount = count(array_filter($allPosts, function($p) { return $p['status'] === 'published'; }));
        $draftCount = count(array_filter($allPosts, function($p) { return $p['status'] === 'draft'; }));
        
        echo "<p>✅ <strong>Posts totales:</strong> " . count($allPosts) . "</p>";
        echo "<p>📝 <strong>Publicados:</strong> " . $publishedCount . "</p>";
        echo "<p>📄 <strong>Borradores:</strong> " . $draftCount . "</p>";
        
        if ($publishedCount > 0 || $draftCount > 0) {
            echo "<p>🎉 <strong>El admin debería mostrar los posts correctamente.</strong></p>";
        }
    }
    
    echo "<p><a href='index.php'>← Volver al Admin</a> | <a href='../index.php'>Ver Blog</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
