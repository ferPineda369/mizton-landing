<?php
/**
 * API para Eliminar Proyectos del Marketplace
 */

require_once __DIR__ . '/../../config/marketplace-config.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db = getMarketplaceDB();

try {
    $projectCode = $_POST['project_code'] ?? '';
    
    if (empty($projectCode)) {
        throw new Exception('Código de proyecto requerido');
    }
    
    // Verificar que el proyecto existe
    $stmt = $db->prepare("SELECT id, name FROM tbl_marketplace_projects WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        throw new Exception('Proyecto no encontrado');
    }
    
    // Eliminar proyecto (ON DELETE CASCADE eliminará automáticamente milestones, documentos, etc.)
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_projects WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Proyecto eliminado exitosamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
