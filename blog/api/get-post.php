<?php
/**
 * API para obtener datos de un post específico (para edición)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';
require_once '../includes/admin-functions.php';

// Verificar acceso de administrador
if (!validateAdminAccess()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de post requerido']);
    exit;
}

$postId = intval($_GET['id']);

try {
    $post = getPostById($postId);
    
    if ($post) {
        echo json_encode([
            'success' => true,
            'post' => $post
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Post no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
