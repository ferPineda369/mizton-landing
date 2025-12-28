<?php
/**
 * API: Registrar eventos de analytics
 * POST /marketplace/api/record-analytics.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action']) || !isset($input['project_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos']);
        exit;
    }
    
    $action = $input['action'];
    $projectId = intval($input['project_id']);
    
    $result = false;
    
    switch ($action) {
        case 'view':
            $result = recordProjectView($projectId);
            break;
        case 'click_through':
            $result = recordProjectClickThrough($projectId);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            exit;
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Evento registrado']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al registrar evento']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar solicitud',
        'message' => $e->getMessage()
    ]);
}
