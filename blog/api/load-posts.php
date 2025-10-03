<?php
/**
 * API para cargar más posts (paginación)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';

// Obtener parámetros
$offset = intval($_GET['offset'] ?? 0);
$limit = intval($_GET['limit'] ?? 6);
$category = $_GET['category'] ?? null;

// Validar límites
$limit = min($limit, 12); // Máximo 12 posts por request
$offset = max($offset, 0);

try {
    // Obtener posts
    $posts = getBlogPosts($limit, $offset, $category);
    
    // Formatear respuesta
    $response = [
        'success' => true,
        'posts' => $posts,
        'count' => count($posts),
        'offset' => $offset,
        'limit' => $limit
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar posts',
        'error' => $e->getMessage()
    ]);
}
?>
