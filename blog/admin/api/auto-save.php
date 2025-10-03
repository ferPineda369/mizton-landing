<?php
/**
 * API para auto-guardado de borradores
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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos inválidos');
    }
    
    // Validar datos mínimos
    if (empty($input['title'])) {
        throw new Exception('Título requerido');
    }
    
    // Preparar datos para auto-guardado
    $draftData = [
        'title' => $input['title'],
        'content' => $input['content'] ?? '',
        'excerpt' => $input['excerpt'] ?? '',
        'category' => $input['category'] ?? 'mizton',
        'tags' => $input['tags'] ?? [],
        'image' => $input['image'] ?? '',
        'featured' => isset($input['featured']) ? (int)$input['featured'] : 0,
        'status' => 'draft'
    ];
    
    // Si hay ID, actualizar; si no, crear nuevo borrador
    if (!empty($input['id']) && is_numeric($input['id'])) {
        $result = updatePost($input['id'], $draftData);
        $message = 'Borrador actualizado automáticamente';
    } else {
        $result = createPost($draftData);
        $message = 'Borrador guardado automáticamente';
    }
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'post_id' => $result['post_id'] ?? $input['id']
        ]);
    } else {
        throw new Exception($result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
