<?php
require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

echo "<h2>Posts disponibles en la base de datos:</h2>";

try {
    $posts = getBlogPosts(20, 0, null, 'all'); // Obtener todos los posts
    
    if (empty($posts)) {
        echo "<p>No hay posts en la base de datos.</p>";
        echo "<p>Ejecutando createSamplePosts()...</p>";
        createSamplePosts();
        $posts = getBlogPosts(20, 0, null, 'all');
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Título</th><th>Slug</th><th>Estado</th><th>URL Limpia</th></tr>";
    
    foreach ($posts as $post) {
        $cleanUrl = "https://mizton.cat/news/" . $post['slug'];
        echo "<tr>";
        echo "<td>" . $post['id'] . "</td>";
        echo "<td>" . htmlspecialchars($post['title']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($post['slug']) . "</strong></td>";
        echo "<td>" . $post['status'] . "</td>";
        echo "<td><a href='" . $cleanUrl . "' target='_blank'>" . $cleanUrl . "</a></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Probar función generateSlug
    echo "<h2>Prueba de generación de slug:</h2>";
    $testTitle = "El Futuro de la Tokenización RWA: Transformando Activos Reales";
    $generatedSlug = generateSlug($testTitle);
    echo "<p><strong>Título:</strong> " . htmlspecialchars($testTitle) . "</p>";
    echo "<p><strong>Slug generado:</strong> " . htmlspecialchars($generatedSlug) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
