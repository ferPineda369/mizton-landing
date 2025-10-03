<?php
/**
 * API para obtener un post específico para edición
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['blog_admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

require_once '../../config/blog-config.php';
require_once '../../includes/blog-functions.php';
require_once '../../includes/admin-functions.php';

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
    $post['tags'] = json_decode($post['tags'], true) ?: [];
    
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
