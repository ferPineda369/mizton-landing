<?php
/**
 * API: Receptor de Webhooks de Proyectos
 * POST /marketplace/api/webhook-receiver.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/sync-functions.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener payload
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'JSON inválido']);
        exit;
    }
    
    // Validar que venga el código del proyecto
    if (empty($data['project_code'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Falta project_code']);
        exit;
    }
    
    // Obtener firma del header
    $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? null;
    
    // Procesar webhook
    $result = processWebhook($data['project_code'], $data, $signature);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar webhook',
        'message' => $e->getMessage()
    ]);
}
