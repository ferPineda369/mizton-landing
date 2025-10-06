<?php
/**
 * API para obtener un post específico para edición
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['blog_admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

require_once dirname(__DIR__) . '/../config/blog-config.php';
require_once dirname(__DIR__) . '/../includes/blog-functions.php';
require_once dirname(__DIR__) . '/../includes/admin-functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de post inválido');
    }
    
    $postId = (int)$_GET['id'];
    $post = getPostById($postId);
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Post no encontrado']);
        exit;
    }
    
    // Decodificar tags JSON
    if (is_string($post['tags'])) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
    } else {
        $post['tags'] = is_array($post['tags']) ? $post['tags'] : [];
    }
    
    echo json_encode([
        'success' => true,
        'post' => $post
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
