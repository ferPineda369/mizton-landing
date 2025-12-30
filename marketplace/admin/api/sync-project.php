<?php
/**
 * API para Sincronizar Proyectos del Marketplace
 */

require_once __DIR__ . '/../../config/marketplace-config.php';
require_once __DIR__ . '/../../includes/sync-functions.php';

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
    if (isset($_POST['sync_all'])) {
        // Sincronizar todos los proyectos con método automático
        $stmt = $db->query("
            SELECT project_code 
            FROM tbl_marketplace_projects 
            WHERE update_method != 'manual'
        ");
        $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($projects as $projectCode) {
            try {
                $result = syncProjectByCode($projectCode);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (Exception $e) {
                $failedCount++;
                error_log("Error sincronizando proyecto $projectCode: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sincronización masiva completada',
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'total' => count($projects)
        ]);
        
    } else {
        // Sincronizar un proyecto específico
        $projectCode = $_POST['project_code'] ?? '';
        
        if (empty($projectCode)) {
            throw new Exception('Código de proyecto requerido');
        }
        
        $result = syncProjectByCode($projectCode);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Proyecto sincronizado exitosamente',
                'data' => $result['data'] ?? null
            ]);
        } else {
            throw new Exception($result['message'] ?? 'Error desconocido en la sincronización');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
