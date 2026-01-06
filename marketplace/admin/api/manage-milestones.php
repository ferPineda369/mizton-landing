<?php
/**
 * API para Gestión de Milestones del Marketplace
 */

require_once __DIR__ . '/../auth-admin.php';
require_once __DIR__ . '/../../config/marketplace-config.php';

header('Content-Type: application/json');

$db = getMarketplaceDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            // Agregar nuevo milestone
            if (!isset($_POST['project_id'], $_POST['title'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            $stmt = $db->prepare("
                INSERT INTO tbl_marketplace_milestones 
                (project_id, title, description, target_date, status, completion_percentage, display_order)
                VALUES (?, ?, ?, ?, ?, ?, 0)
            ");
            
            $stmt->execute([
                $_POST['project_id'],
                $_POST['title'],
                $_POST['description'] ?? '',
                $_POST['target_date'] ?: null,
                $_POST['status'] ?? 'pending',
                $_POST['completion_percentage'] ?? 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Milestone agregado exitosamente',
                'id' => $db->lastInsertId()
            ]);
            break;
            
        case 'update':
            // Actualizar milestone existente
            if (!isset($_POST['id'], $_POST['title'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            $stmt = $db->prepare("
                UPDATE tbl_marketplace_milestones 
                SET title = ?,
                    description = ?,
                    target_date = ?,
                    status = ?,
                    completion_percentage = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'] ?? '',
                $_POST['target_date'] ?: null,
                $_POST['status'] ?? 'pending',
                $_POST['completion_percentage'] ?? 0,
                $_POST['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Milestone actualizado exitosamente'
            ]);
            break;
            
        case 'delete':
            // Eliminar milestone
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $db->prepare("DELETE FROM tbl_marketplace_milestones WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Milestone eliminado exitosamente'
            ]);
            break;
            
        case 'get':
            // Obtener datos de un milestone
            if (!isset($_GET['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $db->prepare("SELECT * FROM tbl_marketplace_milestones WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $milestone = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$milestone) {
                throw new Exception('Milestone no encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'milestone' => $milestone
            ]);
            break;
            
        case 'list':
            // Listar milestones de un proyecto
            if (!isset($_GET['project_id'])) {
                throw new Exception('project_id no especificado');
            }
            
            $stmt = $db->prepare("
                SELECT * FROM tbl_marketplace_milestones 
                WHERE project_id = ? 
                ORDER BY target_date ASC, display_order ASC
            ");
            $stmt->execute([$_GET['project_id']]);
            $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'milestones' => $milestones
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
