<?php
/**
 * Prueba de APIs del admin
 */

session_start();
$_SESSION['blog_admin'] = true; // Simular autenticación para prueba

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';
require_once '../includes/admin-functions.php';

echo "<h2>🧪 Prueba de APIs del Admin</h2>";

try {
    $db = getBlogDB();
    
    // Verificar posts disponibles
    echo "<h3>📊 Posts Disponibles:</h3>";
    $stmt = $db->query("SELECT id, title, status FROM blog_posts ORDER BY id DESC");
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
    
    // Probar API get-post.php
    echo "<h3>🔍 Prueba de get-post.php:</h3>";
    $testPostId = $posts[0]['id'];
    
    echo "<p><strong>Probando con Post ID: {$testPostId}</strong></p>";
    
    // Simular llamada a la API
    $_GET['id'] = $testPostId;
    
    ob_start();
    include 'api/get-post.php';
    $apiResponse = ob_get_clean();
    
    echo "<p><strong>Respuesta de la API:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($apiResponse);
    echo "</pre>";
    
    // Verificar si es JSON válido
    $decoded = json_decode($apiResponse, true);
    if ($decoded) {
        echo "<p>✅ <strong>JSON válido</strong></p>";
        if ($decoded['success']) {
            echo "<p>✅ <strong>API funcionando correctamente</strong></p>";
            echo "<p>Post cargado: " . htmlspecialchars($decoded['post']['title']) . "</p>";
        } else {
            echo "<p>❌ <strong>Error en API:</strong> " . $decoded['error'] . "</p>";
        }
    } else {
        echo "<p>❌ <strong>Respuesta no es JSON válido</strong></p>";
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
    
    echo "<h3>🎯 Conclusión:</h3>";
    echo "<p>Si todas las verificaciones muestran ✅, las APIs deberían funcionar correctamente.</p>";
    echo "<p><a href='index.php'>← Volver al Admin</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>
