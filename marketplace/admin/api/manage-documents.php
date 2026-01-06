<?php
/**
 * API para Gestión de Documentos del Marketplace
 */

require_once __DIR__ . '/../auth-admin.php';
require_once __DIR__ . '/../../config/marketplace-config.php';

header('Content-Type: application/json');

$db = getMarketplaceDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Directorio de subida de archivos
$uploadBaseDir = __DIR__ . '/../../uploads/projects/';

try {
    switch ($action) {
        case 'add':
            // Agregar nuevo documento
            if (!isset($_POST['project_id'], $_POST['document_name'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            $documentUrl = $_POST['document_url'] ?? '';
            
            // Manejar subida de archivo si existe
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document_file'];
                
                // Validar tamaño (10MB máx)
                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception('El archivo es demasiado grande. Máximo 10MB');
                }
                
                // Obtener código del proyecto
                $stmt = $db->prepare("SELECT project_code FROM tbl_marketplace_projects WHERE id = ?");
                $stmt->execute([$_POST['project_id']]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$project) {
                    throw new Exception('Proyecto no encontrado');
                }
                
                // Crear directorio si no existe
                $projectDir = $uploadBaseDir . $project['project_code'] . '/documents/';
                if (!is_dir($projectDir)) {
                    mkdir($projectDir, 0755, true);
                }
                
                // Generar nombre único
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
                $filepath = $projectDir . $filename;
                
                // Mover archivo
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Error al subir el archivo');
                }
                
                // Establecer URL relativa
                $documentUrl = '/marketplace/uploads/projects/' . $project['project_code'] . '/documents/' . $filename;
            }
            
            $stmt = $db->prepare("
                INSERT INTO tbl_marketplace_documents 
                (project_id, document_name, document_type, document_url, file_size, description, 
                 is_public, required_access_level, requires_kyc, min_investment_usd, 
                 coming_soon, available_date, coming_soon_message, display_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['project_id'],
                $_POST['document_name'],
                $_POST['document_type'] ?? 'other',
                $documentUrl,
                $_FILES['document_file']['size'] ?? null,
                $_POST['description'] ?? '',
                isset($_POST['is_public']) ? 1 : 0,
                $_POST['required_access_level'] ?? 'public',
                isset($_POST['requires_kyc']) ? 1 : 0,
                $_POST['min_investment_usd'] ?: null,
                isset($_POST['coming_soon']) ? 1 : 0,
                $_POST['available_date'] ?: null,
                $_POST['coming_soon_message'] ?? '',
                $_POST['display_order'] ?? 0
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Documento agregado exitosamente',
                'id' => $db->lastInsertId(),
                'url' => $documentUrl
            ]);
            break;
            
        case 'update':
            // Actualizar documento existente
            if (!isset($_POST['id'], $_POST['document_name'])) {
                throw new Exception('Faltan datos requeridos');
            }
            
            // Obtener documento actual
            $stmt = $db->prepare("SELECT * FROM tbl_marketplace_documents WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $currentDoc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$currentDoc) {
                throw new Exception('Documento no encontrado');
            }
            
            $documentUrl = $_POST['document_url'] ?? $currentDoc['document_url'];
            $fileSize = $currentDoc['file_size'];
            
            // Manejar subida de nuevo archivo si existe
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['document_file'];
                
                // Validar tamaño
                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception('El archivo es demasiado grande. Máximo 10MB');
                }
                
                // Obtener código del proyecto
                $stmt = $db->prepare("
                    SELECT p.project_code 
                    FROM tbl_marketplace_projects p
                    INNER JOIN tbl_marketplace_documents d ON p.id = d.project_id
                    WHERE d.id = ?
                ");
                $stmt->execute([$_POST['id']]);
                $project = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Crear directorio si no existe
                $projectDir = $uploadBaseDir . $project['project_code'] . '/documents/';
                if (!is_dir($projectDir)) {
                    mkdir($projectDir, 0755, true);
                }
                
                // Eliminar archivo anterior si existe
                if ($currentDoc['document_url'] && file_exists(__DIR__ . '/../..' . $currentDoc['document_url'])) {
                    unlink(__DIR__ . '/../..' . $currentDoc['document_url']);
                }
                
                // Generar nombre único
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
                $filepath = $projectDir . $filename;
                
                // Mover archivo
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Error al subir el archivo');
                }
                
                $documentUrl = '/marketplace/uploads/projects/' . $project['project_code'] . '/documents/' . $filename;
                $fileSize = $file['size'];
            }
            
            $stmt = $db->prepare("
                UPDATE tbl_marketplace_documents 
                SET document_name = ?,
                    document_type = ?,
                    document_url = ?,
                    file_size = ?,
                    description = ?,
                    is_public = ?,
                    required_access_level = ?,
                    requires_kyc = ?,
                    min_investment_usd = ?,
                    coming_soon = ?,
                    available_date = ?,
                    coming_soon_message = ?,
                    display_order = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['document_name'],
                $_POST['document_type'] ?? 'other',
                $documentUrl,
                $fileSize,
                $_POST['description'] ?? '',
                isset($_POST['is_public']) ? 1 : 0,
                $_POST['required_access_level'] ?? 'public',
                isset($_POST['requires_kyc']) ? 1 : 0,
                $_POST['min_investment_usd'] ?: null,
                isset($_POST['coming_soon']) ? 1 : 0,
                $_POST['available_date'] ?: null,
                $_POST['coming_soon_message'] ?? '',
                $_POST['display_order'] ?? 0,
                $_POST['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Documento actualizado exitosamente',
                'url' => $documentUrl
            ]);
            break;
            
        case 'delete':
            // Eliminar documento
            if (!isset($_POST['id'])) {
                throw new Exception('ID no especificado');
            }
            
            // Obtener documento para eliminar archivo físico
            $stmt = $db->prepare("SELECT document_url FROM tbl_marketplace_documents WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($doc && $doc['document_url']) {
                $filepath = __DIR__ . '/../..' . $doc['document_url'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            // Eliminar registro
            $stmt = $db->prepare("DELETE FROM tbl_marketplace_documents WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);
            break;
            
        case 'get':
            // Obtener datos de un documento
            if (!isset($_GET['id'])) {
                throw new Exception('ID no especificado');
            }
            
            $stmt = $db->prepare("SELECT * FROM tbl_marketplace_documents WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception('Documento no encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'document' => $document
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
