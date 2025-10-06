<?php
/**
 * API para auto-guardado de borradores
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';
require_once '../includes/admin-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar acceso de administrador
if (!validateAdminAccess()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

try {
    $result = autoSaveDraft($_POST);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Borrador guardado automáticamente',
            'timestamp' => date('H:i:s')
        ]);
    } else {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en auto-guardado: ' . $e->getMessage()
    ]);
}
?>
