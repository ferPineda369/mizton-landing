<?php
/**
 * Funciones de Control de Acceso para Inversionistas
 * Sistema de verificación de permisos para documentos del marketplace
 */

require_once __DIR__ . '/../config/marketplace-config.php';

/**
 * Verificar si un usuario es inversionista de un proyecto
 * 
 * @param int $projectId ID del proyecto
 * @param int|null $userId ID del usuario (si está logueado)
 * @param string|null $walletAddress Dirección de wallet (si no está logueado)
 * @return array|false Datos del inversionista o false si no es inversionista
 */
function isProjectInvestor($projectId, $userId = null, $walletAddress = null) {
    $db = getMarketplaceDB();
    
    error_log("DEBUG isProjectInvestor - ProjectID: $projectId, UserID: $userId, Wallet: $walletAddress");
    
    // Si hay userId, buscar por user_id o por wallet asociada
    if ($userId) {
        // Primero intentar por user_id directo
        $stmt = $db->prepare("
            SELECT * FROM tbl_marketplace_project_investors 
            WHERE project_id = ? AND user_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$projectId, $userId]);
        $investor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($investor) {
            error_log("DEBUG: Inversionista encontrado por user_id - Nivel: {$investor['access_level']}");
            return $investor;
        }
        
        error_log("DEBUG: No encontrado por user_id, buscando por wallet");
        
        // Si no encontró, buscar por wallet del usuario usando tabla wallet
        $stmt = $db->prepare("
            SELECT i.* 
            FROM tbl_marketplace_project_investors i
            INNER JOIN wallet w ON i.wallet_address = w.address
            WHERE i.project_id = ? AND w.userId = ? AND i.is_active = TRUE
        ");
        $stmt->execute([$projectId, $userId]);
        $investor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($investor) {
            error_log("DEBUG: Inversionista encontrado por wallet - Nivel: {$investor['access_level']}");
        } else {
            error_log("DEBUG: No encontrado por wallet tampoco");
        }
        
        return $investor;
    }
    
    // Si hay wallet address, buscar por wallet
    if ($walletAddress) {
        $stmt = $db->prepare("
            SELECT * FROM tbl_marketplace_project_investors 
            WHERE project_id = ? AND wallet_address = ? AND is_active = TRUE
        ");
        $stmt->execute([$projectId, strtolower($walletAddress)]);
        $investor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($investor) {
            error_log("DEBUG: Inversionista encontrado por wallet externa - Nivel: {$investor['access_level']}");
        }
        
        return $investor;
    }
    
    error_log("DEBUG: No se proporcionó userId ni walletAddress");
    return false;
}

/**
 * Verificar si un usuario tiene acceso a un documento
 * 
 * @param int $documentId ID del documento
 * @param int|null $userId ID del usuario
 * @param string|null $walletAddress Dirección de wallet
 * @return array ['access' => bool, 'reason' => string, 'investor' => array|null]
 */
function checkDocumentAccess($documentId, $userId = null, $walletAddress = null) {
    $db = getMarketplaceDB();
    
    // Obtener información del documento
    $stmt = $db->prepare("
        SELECT d.*, p.project_code, p.name as project_name
        FROM tbl_marketplace_documents d
        INNER JOIN tbl_marketplace_projects p ON d.project_id = p.id
        WHERE d.id = ?
    ");
    $stmt->execute([$documentId]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        return [
            'access' => false,
            'reason' => 'Documento no encontrado',
            'investor' => null
        ];
    }
    
    // Si el documento es público (is_public = TRUE y required_access_level = 'public')
    if ($document['is_public'] && $document['required_access_level'] === 'public') {
        return [
            'access' => true,
            'reason' => 'Documento público',
            'investor' => null,
            'document' => $document
        ];
    }
    
    // Si no es público, verificar si es inversionista
    $investor = isProjectInvestor($document['project_id'], $userId, $walletAddress);
    
    if (!$investor) {
        return [
            'access' => false,
            'reason' => 'Debe ser inversionista del proyecto para acceder a este documento',
            'investor' => null,
            'document' => $document,
            'required_level' => $document['required_access_level']
        ];
    }
    
    // Verificar nivel de acceso
    $accessLevels = ['public', 'basic', 'standard', 'premium', 'vip', 'founder'];
    $requiredLevel = $document['required_access_level'] ?? 'public';
    $investorLevel = $investor['access_level'];
    
    $requiredIndex = array_search($requiredLevel, $accessLevels);
    $investorIndex = array_search($investorLevel, $accessLevels);
    
    // Debug: registrar en error_log para diagnóstico
    error_log("DEBUG checkDocumentAccess - Document: {$document['document_name']}, Required: $requiredLevel ($requiredIndex), Investor: $investorLevel ($investorIndex)");
    
    if ($requiredIndex === false || $investorIndex === false) {
        error_log("ERROR: Nivel de acceso inválido - Required: $requiredLevel, Investor: $investorLevel");
        return [
            'access' => false,
            'reason' => 'Error en configuración de niveles de acceso',
            'investor' => $investor,
            'document' => $document
        ];
    }
    
    if ($investorIndex < $requiredIndex) {
        return [
            'access' => false,
            'reason' => "Este documento requiere nivel de acceso '$requiredLevel'. Su nivel actual es '$investorLevel'",
            'investor' => $investor,
            'document' => $document,
            'required_level' => $requiredLevel,
            'current_level' => $investorLevel
        ];
    }
    
    // Verificar si requiere KYC (solo si está configurado)
    if (!empty($document['requires_kyc']) && $investor['kyc_status'] !== 'approved') {
        return [
            'access' => false,
            'reason' => 'Este documento requiere verificación KYC aprobada',
            'investor' => $investor,
            'document' => $document,
            'kyc_required' => true
        ];
    }
    
    // Verificar inversión mínima (solo si está configurado en el DOCUMENTO, no en el nivel)
    if (!empty($document['min_investment_usd']) && $investor['investment_usd'] < $document['min_investment_usd']) {
        return [
            'access' => false,
            'reason' => sprintf(
                'Este documento requiere una inversión mínima de $%s USD. Su inversión actual es de $%s USD',
                number_format($document['min_investment_usd'], 2),
                number_format($investor['investment_usd'], 2)
            ),
            'investor' => $investor,
            'document' => $document,
            'min_investment' => $document['min_investment_usd']
        ];
    }
    
    // Acceso concedido
    return [
        'access' => true,
        'reason' => 'Acceso concedido',
        'investor' => $investor,
        'document' => $document
    ];
}

/**
 * Registrar acceso a documento (para auditoría)
 * 
 * @param int $documentId ID del documento
 * @param string $accessType 'view', 'download', 'denied'
 * @param bool $accessGranted Si se concedió el acceso
 * @param int|null $investorId ID del inversionista
 * @param int|null $userId ID del usuario
 * @param string|null $denialReason Razón de denegación
 */
function logDocumentAccess($documentId, $accessType, $accessGranted, $investorId = null, $userId = null, $denialReason = null) {
    $db = getMarketplaceDB();
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_document_access_logs 
        (document_id, investor_id, user_id, access_type, access_granted, denial_reason, ip_address, user_agent, referer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $documentId,
        $investorId,
        $userId,
        $accessType,
        $accessGranted ? 1 : 0,
        $denialReason,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_REFERER'] ?? null
    ]);
    
    // Si fue descarga exitosa, actualizar contador
    if ($accessGranted && $accessType === 'download') {
        $stmt = $db->prepare("
            UPDATE tbl_marketplace_documents 
            SET download_count = download_count + 1,
                last_downloaded_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$documentId]);
    }
}

/**
 * Obtener documentos accesibles para un inversionista
 * 
 * @param int $projectId ID del proyecto
 * @param int|null $userId ID del usuario
 * @param string|null $walletAddress Dirección de wallet
 * @return array Lista de documentos con indicador de acceso
 */
function getAccessibleDocuments($projectId, $userId = null, $walletAddress = null) {
    $db = getMarketplaceDB();
    
    // Obtener todos los documentos del proyecto
    $stmt = $db->prepare("
        SELECT * FROM tbl_marketplace_documents 
        WHERE project_id = ? 
        ORDER BY display_order ASC, document_name ASC
    ");
    $stmt->execute([$projectId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar inversionista
    $investor = isProjectInvestor($projectId, $userId, $walletAddress);
    
    // Procesar cada documento
    foreach ($documents as &$doc) {
        $accessCheck = checkDocumentAccess($doc['id'], $userId, $walletAddress);
        $doc['has_access'] = $accessCheck['access'];
        $doc['access_reason'] = $accessCheck['reason'];
        $doc['is_investor'] = $investor !== false;
        $doc['investor_level'] = $investor ? $investor['access_level'] : null;
    }
    
    return $documents;
}

/**
 * Obtener información del nivel de acceso
 * 
 * @param string $levelKey Clave del nivel (basic, standard, premium, etc)
 * @return array|false Información del nivel o false
 */
function getAccessLevelInfo($levelKey) {
    $db = getMarketplaceDB();
    
    $stmt = $db->prepare("
        SELECT * FROM tbl_marketplace_access_levels 
        WHERE level_key = ? AND is_active = TRUE
    ");
    $stmt->execute([$levelKey]);
    $level = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($level && $level['benefits']) {
        $level['benefits'] = json_decode($level['benefits'], true);
    }
    
    return $level;
}

/**
 * Obtener todos los niveles de acceso
 * 
 * @return array Lista de niveles ordenados
 */
function getAllAccessLevels() {
    $db = getMarketplaceDB();
    
    $stmt = $db->query("
        SELECT * FROM tbl_marketplace_access_levels 
        WHERE is_active = TRUE 
        ORDER BY display_order ASC
    ");
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($levels as &$level) {
        if ($level['benefits']) {
            $level['benefits'] = json_decode($level['benefits'], true);
        }
    }
    
    return $levels;
}

/**
 * Obtener inversionistas de un proyecto
 * 
 * @param int $projectId ID del proyecto
 * @param bool $activeOnly Solo inversionistas activos
 * @return array Lista de inversionistas
 */
function getProjectInvestors($projectId, $activeOnly = true) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM vw_marketplace_investors WHERE project_id = ?";
    if ($activeOnly) {
        $sql .= " AND is_active = TRUE";
    }
    $sql .= " ORDER BY investment_usd DESC, created_at ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$projectId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Agregar inversionista manualmente (por admin)
 * 
 * @param array $data Datos del inversionista
 * @return int|false ID del inversionista creado o false
 */
function addProjectInvestor($data) {
    $db = getMarketplaceDB();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO tbl_marketplace_project_investors 
            (project_id, user_id, wallet_address, blockchain_network, token_amount, 
             investment_usd, investment_date, access_level, investor_type, 
             registration_method, notes, is_verified, verified_by, verified_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $data['project_id'],
            $data['user_id'] ?? null,
            strtolower($data['wallet_address']),
            $data['blockchain_network'] ?? 'BSC',
            $data['token_amount'] ?? 0,
            $data['investment_usd'] ?? 0,
            $data['investment_date'] ?? date('Y-m-d'),
            $data['access_level'] ?? 'basic',
            $data['investor_type'] ?? 'external_wallet',
            'manual_admin',
            $data['notes'] ?? null,
            $data['is_verified'] ?? true,
            $data['verified_by'] ?? null,
            $data['created_by'] ?? null
        ]);
        
        $investorId = $db->lastInsertId();
        
        // Registrar en log
        logInvestorAction($investorId, 'created', 'Inversionista agregado manualmente', $data['created_by'] ?? null);
        
        return $investorId;
        
    } catch (PDOException $e) {
        error_log("Error al agregar inversionista: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar inversionista
 * 
 * @param int $investorId ID del inversionista
 * @param array $data Datos a actualizar
 * @param int|null $updatedBy ID del admin que actualiza
 * @return bool
 */
function updateProjectInvestor($investorId, $data, $updatedBy = null) {
    $db = getMarketplaceDB();
    
    try {
        // Obtener valores anteriores para log
        $stmt = $db->prepare("SELECT * FROM tbl_marketplace_project_investors WHERE id = ?");
        $stmt->execute([$investorId]);
        $oldValues = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Construir UPDATE dinámico
        $fields = [];
        $values = [];
        
        $allowedFields = ['token_amount', 'investment_usd', 'investment_date', 'access_level', 
                         'kyc_status', 'aml_status', 'notes', 'is_active', 'is_verified'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $investorId;
        
        $sql = "UPDATE tbl_marketplace_project_investors SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        // Registrar en log
        logInvestorAction($investorId, 'updated', 'Inversionista actualizado', $updatedBy, $oldValues, $data);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error al actualizar inversionista: " . $e->getMessage());
        return false;
    }
}

/**
 * Registrar acción sobre inversionista (para auditoría)
 */
function logInvestorAction($investorId, $actionType, $description, $performedBy = null, $oldValues = null, $newValues = null) {
    $db = getMarketplaceDB();
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_investor_logs 
        (investor_id, action_type, action_description, old_values, new_values, performed_by, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $investorId,
        $actionType,
        $description,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $performedBy,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

/**
 * Obtener wallet address de un usuario Mizton
 * 
 * @param int $userId ID del usuario
 * @return string|false Wallet address o false
 */
function getUserWalletAddress($userId) {
    $db = getMarketplaceDB();
    
    $stmt = $db->prepare("SELECT address FROM wallet WHERE userId = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['address'] : false;
}
