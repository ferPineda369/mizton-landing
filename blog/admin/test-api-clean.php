<?php
/**
 * Prueba limpia de APIs del admin sin conflictos de headers
 */

session_start();
$_SESSION['blog_admin'] = true; // Simular autenticación

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';
require_once '../includes/admin-functions.php';

echo "<h2>🧪 Prueba Limpia de APIs del Admin</h2>";

try {
    $db = getBlogDB();
    
    // Verificar posts disponibles
    echo "<h3>📊 Posts Disponibles:</h3>";
    $stmt = $db->query("SELECT id, title, status, tags FROM blog_posts ORDER BY id DESC");
    $posts = $stmt->fetchAll();
    
    if (empty($posts)) {
        echo "<p>❌ No hay posts para probar. <a href='../setup-initial-content.php'>Crear contenido</a></p>";
        exit;
    }
    
    echo "<ul>";
    foreach ($posts as $post) {
        echo "<li>ID: {$post['id']} - {$post['title']} ({$post['status']})</li>";
    }
    echo "</ul>";
    
    // Probar función getPostById directamente
    echo "<h3>🔍 Prueba de función getPostById():</h3>";
    $testPostId = $posts[0]['id'];
    
    echo "<p><strong>Probando con Post ID: {$testPostId}</strong></p>";
    
    $post = getPostById($testPostId);
    if ($post) {
        echo "<p>✅ <strong>Post encontrado:</strong> " . htmlspecialchars($post['title']) . "</p>";
        echo "<p><strong>Tags originales:</strong> " . htmlspecialchars($post['tags']) . "</p>";
        echo "<p><strong>Tipo de tags:</strong> " . gettype($post['tags']) . "</p>";
        
        // Probar decodificación de tags
        if (is_string($post['tags'])) {
            $decodedTags = json_decode($post['tags'], true);
            echo "<p><strong>Tags decodificados:</strong> " . (is_array($decodedTags) ? implode(', ', $decodedTags) : 'Error en decodificación') . "</p>";
        } else {
            echo "<p><strong>Tags ya son array:</strong> " . (is_array($post['tags']) ? implode(', ', $post['tags']) : 'No es array') . "</p>";
        }
    } else {
        echo "<p>❌ <strong>Post no encontrado</strong></p>";
    }
    
    // Verificar archivos API
    echo "<h3>📁 Verificación de Archivos API:</h3>";
    $apiFiles = [
        'api/get-post.php',
        'api/auto-save.php',
        'api/upload-image.php'
    ];
    
    foreach ($apiFiles as $file) {
        if (file_exists($file)) {
            echo "<p>✅ {$file} - EXISTE</p>";
        } else {
            echo "<p>❌ {$file} - NO EXISTE</p>";
        }
    }
    
    // Verificar funciones necesarias
    echo "<h3>🔧 Verificación de Funciones:</h3>";
    $functions = ['getPostById', 'createPost', 'updatePost', 'deletePost'];
    
    foreach ($functions as $func) {
        if (function_exists($func)) {
            echo "<p>✅ {$func}() - EXISTE</p>";
        } else {
            echo "<p>❌ {$func}() - NO EXISTE</p>";
        }
    }
    
    echo "<h3>🎯 Prueba de API Real:</h3>";
    echo "<p><a href='api/test-direct.php' target='_blank'>🧪 Probar get-post.php directamente</a></p>";
    
    echo "<h3>✅ Conclusión:</h3>";
    echo "<p>Si todas las verificaciones muestran ✅, las APIs deberían funcionar en el admin.</p>";
    echo "<p><a href='index.php'>← Volver al Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
