<?php
/**
 * API para Guardar Metadata de Proyectos
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../auth-admin.php';
require_once __DIR__ . '/../../config/marketplace-config.php';
require_once __DIR__ . '/../../config/project-types-config.php';
require_once __DIR__ . '/../../includes/project-metadata-functions.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Obtener datos
$projectId = $_POST['project_id'] ?? null;
$metadata = $_POST['metadata'] ?? [];

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'ID de proyecto requerido']);
    exit;
}

$db = getMarketplaceDB();

try {
    // Verificar que el proyecto existe
    $stmt = $db->prepare("SELECT id, project_type FROM tbl_marketplace_projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Proyecto no encontrado']);
        exit;
    }
    
    // Obtener configuración del tipo de proyecto
    $typeConfig = getProjectTypeConfig($project['project_type']);
    $metadataFields = $typeConfig['metadata_fields'];
    
    // Validar y guardar metadata
    $savedCount = 0;
    foreach ($metadata as $key => $value) {
        // Verificar que el campo existe en la configuración
        if (!isset($metadataFields[$key])) {
            continue;
        }
        
        $fieldConfig = $metadataFields[$key];
        
        // Validar campo requerido
        if ($fieldConfig['required'] && empty($value)) {
            echo json_encode([
                'success' => false, 
                'message' => "El campo '{$fieldConfig['label']}' es requerido"
            ]);
            exit;
        }
        
        // Determinar tipo de metadata
        $metaType = 'text';
        switch ($fieldConfig['type']) {
            case 'number':
                $metaType = 'number';
                break;
            case 'date':
                $metaType = 'date';
                break;
            case 'url':
                $metaType = 'url';
                break;
            case 'json':
            case 'multiselect':
                $metaType = 'json';
                // Validar JSON si es necesario
                if (!empty($value) && $metaType === 'json') {
                    $decoded = json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo json_encode([
                            'success' => false,
                            'message' => "El campo '{$fieldConfig['label']}' debe ser JSON válido"
                        ]);
                        exit;
                    }
                }
                break;
            case 'checkbox':
                $metaType = 'boolean';
                $value = $value ? '1' : '0';
                break;
        }
        
        // Guardar metadata
        if (saveProjectMetadata($projectId, $key, $value, $metaType)) {
            $savedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Metadata guardada exitosamente ($savedCount campos)",
        'saved_count' => $savedCount
    ]);
    
} catch (Exception $e) {
    error_log("Error saving metadata: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar metadata: ' . $e->getMessage()
    ]);
}
