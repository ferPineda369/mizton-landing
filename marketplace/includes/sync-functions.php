<?php
/**
 * Funciones de Sincronización - Marketplace Mizton
 * Manejo de API Pull, Webhooks y lectura Blockchain
 */

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/marketplace-functions.php';

/**
 * Sincronizar datos de un proyecto específico
 */
function syncProjectData($projectId) {
    $project = getProjectById($projectId);
    
    if (!$project) {
        return ['success' => false, 'error' => 'Proyecto no encontrado'];
    }
    
    $startTime = microtime(true);
    $db = getMarketplaceDB();
    
    // Actualizar timestamp de intento
    $db->prepare("UPDATE tbl_marketplace_projects SET last_sync_attempt = NOW() WHERE id = ?")
       ->execute([$projectId]);
    
    $result = null;
    
    switch ($project['update_method']) {
        case 'api_pull':
            $result = syncViaAPI($project);
            break;
        case 'blockchain':
            $result = syncViaBlockchain($project);
            break;
        case 'manual':
            return ['success' => false, 'error' => 'Proyecto configurado para actualización manual'];
        default:
            return ['success' => false, 'error' => 'Método de actualización no válido'];
    }
    
    $executionTime = round((microtime(true) - $startTime) * 1000);
    
    // Registrar en log
    logSync($projectId, $project['update_method'], $result, $executionTime);
    
    return $result;
}

/**
 * Sincronizar vía API Pull
 */
function syncViaAPI($project) {
    if (empty($project['api_endpoint'])) {
        return ['success' => false, 'error' => 'No hay endpoint API configurado'];
    }
    
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    
    // Agregar API Key si existe
    if (!empty($project['api_key'])) {
        $headers[] = 'X-API-Key: ' . $project['api_key'];
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $project['api_endpoint'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => SYNC_TIMEOUT,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'HTTP Error: ' . $httpCode,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Respuesta JSON inválida: ' . json_last_error_msg(),
            'response' => $response
        ];
    }
    
    // Validar estructura de datos
    $validationErrors = validateProjectData($data);
    if (!empty($validationErrors)) {
        return [
            'success' => false,
            'error' => 'Datos inválidos',
            'validation_errors' => $validationErrors,
            'data' => $data
        ];
    }
    
    // Actualizar cache
    if (updateProjectCache($project['id'], $data)) {
        return [
            'success' => true,
            'message' => 'Datos sincronizados correctamente',
            'data' => $data
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Error al actualizar cache en base de datos'
        ];
    }
}

/**
 * Sincronizar vía Blockchain (lectura directa del smart contract)
 */
function syncViaBlockchain($project) {
    if (empty($project['contract_address']) || empty($project['blockchain_network'])) {
        return ['success' => false, 'error' => 'Faltan datos de blockchain'];
    }
    
    // TODO: Implementar lectura de blockchain usando Web3
    // Por ahora retornamos error indicando que está en desarrollo
    
    return [
        'success' => false,
        'error' => 'Sincronización blockchain en desarrollo',
        'note' => 'Requiere implementación de Web3.php o integración con servicio RPC'
    ];
}

/**
 * Procesar webhook recibido
 */
function processWebhook($projectCode, $payload, $signature = null) {
    $project = getProjectByCode($projectCode);
    
    if (!$project) {
        return ['success' => false, 'error' => 'Proyecto no encontrado'];
    }
    
    // Validar firma si está configurada
    if (!empty($project['api_secret']) && $signature) {
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $project['api_secret']);
        if (!hash_equals($expectedSignature, $signature)) {
            return ['success' => false, 'error' => 'Firma inválida'];
        }
    }
    
    $db = getMarketplaceDB();
    
    // Guardar webhook en tabla
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_webhooks 
        (project_id, payload, signature, event_type, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $project['id'],
        json_encode($payload),
        $signature,
        $payload['event_type'] ?? 'update',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    $webhookId = $db->lastInsertId();
    
    // Validar estructura de datos
    $validationErrors = validateProjectData($payload);
    if (!empty($validationErrors)) {
        // Marcar webhook como procesado con error
        $db->prepare("UPDATE tbl_marketplace_webhooks SET processed = TRUE, processing_error = ? WHERE id = ?")
           ->execute([json_encode($validationErrors), $webhookId]);
        
        return [
            'success' => false,
            'error' => 'Datos inválidos',
            'validation_errors' => $validationErrors
        ];
    }
    
    // Actualizar cache del proyecto
    if (updateProjectCache($project['id'], $payload)) {
        // Marcar webhook como procesado exitosamente
        $db->prepare("UPDATE tbl_marketplace_webhooks SET processed = TRUE, processed_at = NOW() WHERE id = ?")
           ->execute([$webhookId]);
        
        // Registrar en log de sincronización
        logSync($project['id'], 'webhook', [
            'success' => true,
            'message' => 'Webhook procesado correctamente',
            'data' => $payload
        ], 0);
        
        return [
            'success' => true,
            'message' => 'Webhook procesado correctamente'
        ];
    } else {
        $db->prepare("UPDATE tbl_marketplace_webhooks SET processed = TRUE, processing_error = ? WHERE id = ?")
           ->execute(['Error al actualizar cache', $webhookId]);
        
        return [
            'success' => false,
            'error' => 'Error al actualizar cache en base de datos'
        ];
    }
}

/**
 * Sincronizar todos los proyectos que usan API Pull
 */
function syncAllProjects() {
    $db = getMarketplaceDB();
    
    // Obtener proyectos que requieren sincronización
    $stmt = $db->query("
        SELECT id, project_code, name, update_method, update_frequency, last_successful_sync
        FROM tbl_marketplace_projects
        WHERE update_method IN ('api_pull', 'blockchain')
        AND is_active = TRUE
    ");
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = [];
    
    foreach ($projects as $project) {
        // Verificar si es momento de sincronizar
        if ($project['last_successful_sync']) {
            $lastSync = strtotime($project['last_successful_sync']);
            $frequency = $project['update_frequency'] * 60; // convertir a segundos
            $nextSync = $lastSync + $frequency;
            
            if (time() < $nextSync) {
                $results[$project['project_code']] = [
                    'skipped' => true,
                    'reason' => 'No es momento de sincronizar',
                    'next_sync' => date('Y-m-d H:i:s', $nextSync)
                ];
                continue;
            }
        }
        
        // Sincronizar proyecto
        $result = syncProjectData($project['id']);
        $results[$project['project_code']] = $result;
        
        // Pequeña pausa entre sincronizaciones para no saturar
        usleep(500000); // 0.5 segundos
    }
    
    return $results;
}

/**
 * Registrar sincronización en log
 */
function logSync($projectId, $method, $result, $executionTime = 0) {
    $db = getMarketplaceDB();
    
    $status = $result['success'] ? 'success' : 'failed';
    $errorMessage = $result['error'] ?? null;
    $responseData = isset($result['data']) ? json_encode($result['data']) : null;
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_sync_log 
        (project_id, sync_method, status, response_data, error_message, execution_time_ms)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $projectId,
        $method,
        $status,
        $responseData,
        $errorMessage,
        $executionTime
    ]);
}

/**
 * Obtener logs de sincronización de un proyecto
 */
function getSyncLogs($projectId, $limit = 50) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("
        SELECT * FROM tbl_marketplace_sync_log
        WHERE project_id = ?
        ORDER BY sync_timestamp DESC
        LIMIT ?
    ");
    $stmt->execute([$projectId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener estado de sincronización de todos los proyectos
 */
function getSyncStatus() {
    $db = getMarketplaceDB();
    $stmt = $db->query("SELECT * FROM vw_marketplace_sync_status ORDER BY minutes_since_last_sync DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Limpiar logs antiguos (mantener solo últimos 30 días)
 */
function cleanOldSyncLogs($days = 30) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("
        DELETE FROM tbl_marketplace_sync_log
        WHERE sync_timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    $stmt->execute([$days]);
    return $stmt->rowCount();
}

/**
 * Reintentar sincronización fallida
 */
function retrySyncFailures($maxRetries = MAX_SYNC_RETRIES) {
    $db = getMarketplaceDB();
    
    // Obtener proyectos con sincronización fallida reciente
    $stmt = $db->query("
        SELECT DISTINCT p.id, p.project_code
        FROM tbl_marketplace_projects p
        INNER JOIN tbl_marketplace_sync_log l ON p.id = l.project_id
        WHERE l.status = 'failed'
        AND l.sync_timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        AND p.update_method IN ('api_pull', 'blockchain')
        AND p.is_active = TRUE
        GROUP BY p.id
        HAVING COUNT(*) < ?
    ", [$maxRetries]);
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = [];
    
    foreach ($projects as $project) {
        $result = syncProjectData($project['id']);
        $results[$project['project_code']] = $result;
    }
    
    return $results;
}
