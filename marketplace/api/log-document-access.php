<?php
/**
 * API para Registrar Acceso a Documentos
 * Registra descargas y accesos denegados para auditoría
 */

session_start();
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/investor-access-functions.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $documentId = $data['document_id'] ?? null;
    $accessType = $data['access_type'] ?? 'view';
    
    if (!$documentId) {
        throw new Exception('ID de documento requerido');
    }
    
    // Obtener información del usuario
    $userId = $_SESSION['idUser'] ?? null;
    $walletAddress = null;
    
    if ($userId) {
        $walletAddress = getUserWalletAddress($userId);
    }
    
    // Verificar acceso
    $accessCheck = checkDocumentAccess($documentId, $userId, $walletAddress);
    
    // Obtener ID del inversionista si existe
    $investorId = null;
    if ($accessCheck['investor']) {
        $investorId = $accessCheck['investor']['id'];
    }
    
    // Registrar el acceso
    logDocumentAccess(
        $documentId,
        $accessType,
        $accessCheck['access'],
        $investorId,
        $userId,
        $accessCheck['access'] ? null : $accessCheck['reason']
    );
    
    echo json_encode([
        'success' => true,
        'access_granted' => $accessCheck['access'],
        'message' => $accessCheck['reason']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
