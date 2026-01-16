<?php
/**
 * Funciones principales del Marketplace Mizton
 */

require_once __DIR__ . '/../config/marketplace-config.php';

/**
 * Obtener todos los proyectos activos
 */
function getActiveProjects($filters = []) {
    $db = getMarketplaceDB();
    
    $sql = "SELECT * FROM vw_marketplace_active_projects WHERE 1=1";
    $params = [];
    
    // Filtro por categoría
    if (!empty($filters['category'])) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }
    
    // Filtro por estado
    if (!empty($filters['status'])) {
        $sql .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    // Búsqueda por texto
    if (!empty($filters['search'])) {
        $sql .= " AND (name LIKE ? OR description LIKE ? OR project_code LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Filtro por destacados
    if (isset($filters['featured']) && $filters['featured']) {
        $sql .= " AND featured = TRUE";
    }
    
    // Ordenamiento
    $orderBy = $filters['order_by'] ?? 'default';
    switch ($orderBy) {
        case 'funding':
            $sql .= " ORDER BY funding_percentage DESC";
            break;
        case 'apy':
            $sql .= " ORDER BY apy_percentage DESC";
            break;
        case 'price_asc':
            $sql .= " ORDER BY token_price_usd ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY token_price_usd DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY created_at DESC";
            break;
        default:
            $sql .= " ORDER BY featured DESC, featured_order ASC, display_order ASC";
    }
    
    // Paginación
    if (isset($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];
        
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener proyecto por slug
 */
function getProjectBySlug($slug) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("SELECT * FROM tbl_marketplace_projects WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener proyecto por ID
 */
function getProjectById($id) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("SELECT * FROM tbl_marketplace_projects WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener proyecto por código
 */
function getProjectByCode($code) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("SELECT * FROM tbl_marketplace_projects WHERE project_code = ?");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtener proyecto completo con secciones y metadata
 * Usado por project-landing.php y marketplace-reserve.php
 */
function getCompleteProject($projectIdentifier) {
    $db = getMarketplaceDB();
    
    // Determinar si es ID o código
    if (is_numeric($projectIdentifier)) {
        $project = getProjectById($projectIdentifier);
    } else {
        $project = getProjectByCode($projectIdentifier);
    }
    
    if (!$project) {
        return null;
    }
    
    // Inicializar secciones vacías
    $project['sections'] = [];
    
    // Intentar obtener secciones de la landing page (si la tabla existe)
    try {
        $stmt = $db->prepare("
            SELECT * FROM tbl_marketplace_landing_sections 
            WHERE project_id = ? AND is_active = TRUE
            ORDER BY display_order ASC
        ");
        $stmt->execute([$project['id']]);
        $project['sections'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parsear section_data JSON
        foreach ($project['sections'] as &$section) {
            if (!empty($section['section_data'])) {
                $section['section_data'] = json_decode($section['section_data'], true);
            }
        }
    } catch (PDOException $e) {
        // Tabla no existe, continuar sin secciones
        $project['sections'] = [];
    }
    
    // Inicializar metadata vacía
    $project['metadata'] = [];
    
    // Intentar obtener metadata del proyecto (si la tabla existe)
    try {
        $stmt = $db->prepare("
            SELECT meta_key, meta_value, meta_type 
            FROM tbl_marketplace_project_metadata 
            WHERE project_id = ?
        ");
        $stmt->execute([$project['id']]);
        $metadata = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($metadata as $meta) {
            $project['metadata'][$meta['meta_key']] = [
                'value' => $meta['meta_value'],
                'type' => $meta['meta_type']
            ];
        }
    } catch (PDOException $e) {
        // Tabla no existe, continuar sin metadata
        $project['metadata'] = [];
    }

    // Parsear gallery_images si existe
    if (!empty($project['gallery_images'])) {
        $project['gallery_images'] = json_decode($project['gallery_images'], true);
    }
    
    return $project;
}

/**
 * Obtener datos cacheados del proyecto (JSON parseado)
 */
function getProjectCachedData($projectId) {
    $project = getProjectById($projectId);
    if ($project && $project['cached_data']) {
        return json_decode($project['cached_data'], true);
    }
    return null;
}

/**
 * Actualizar cache de datos del proyecto
 */
function updateProjectCache($projectId, $data) {
    $db = getMarketplaceDB();
    
    // Extraer campos principales para búsquedas rápidas
    $updates = [
        'cached_data' => json_encode($data),
        'last_successful_sync' => date('Y-m-d H:i:s'),
        'sync_status' => 'success'
    ];
    
    // Extraer datos financieros si existen
    if (isset($data['blockchain'])) {
        $updates['token_price_usd'] = $data['blockchain']['token_price_usd'] ?? null;
        $updates['total_supply'] = $data['blockchain']['total_supply'] ?? null;
        $updates['circulating_supply'] = $data['blockchain']['circulating_supply'] ?? null;
        $updates['market_cap'] = $data['blockchain']['market_cap'] ?? null;
    }
    
    if (isset($data['financials'])) {
        $updates['funding_goal'] = $data['financials']['funding_goal'] ?? null;
        $updates['funding_raised'] = $data['financials']['raised'] ?? null;
        $updates['funding_percentage'] = $data['financials']['funding_percentage'] ?? null;
        $updates['apy_percentage'] = $data['financials']['apy_staking'] ?? $data['financials']['roi_projected'] ?? null;
    }
    
    if (isset($data['participation'])) {
        $updates['holders_count'] = $data['participation']['holders_count'] ?? null;
    }
    
    // Construir query
    $fields = [];
    $values = [];
    foreach ($updates as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $projectId;
    
    $sql = "UPDATE tbl_marketplace_projects SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Obtener documentos de un proyecto
 */
function getProjectDocuments($projectId, $publicOnly = true) {
    $db = getMarketplaceDB();
    $sql = "SELECT * FROM tbl_marketplace_documents WHERE project_id = ?";
    $params = [$projectId];
    
    if ($publicOnly) {
        $sql .= " AND is_public = TRUE";
    }
    
    $sql .= " ORDER BY display_order ASC, uploaded_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener milestones de un proyecto
 */
function getProjectMilestones($projectId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("
        SELECT * FROM tbl_marketplace_milestones 
        WHERE project_id = ? 
        ORDER BY display_order ASC
    ");
    $stmt->execute([$projectId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtener categorías activas
 */
function getActiveCategories() {
    $db = getMarketplaceDB();
    $stmt = $db->query("
        SELECT * FROM tbl_marketplace_categories 
        WHERE is_active = TRUE 
        ORDER BY display_order ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Contar proyectos por categoría
 */
function countProjectsByCategory() {
    $db = getMarketplaceDB();
    $stmt = $db->query("
        SELECT 
            category,
            COUNT(*) as count
        FROM tbl_marketplace_projects
        WHERE is_active = TRUE
        GROUP BY category
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $counts = [];
    foreach ($results as $row) {
        $counts[$row['category']] = $row['count'];
    }
    return $counts;
}

/**
 * Obtener proyectos destacados
 */
function getFeaturedProjects($limit = null) {
    $limit = $limit ?? getMarketplaceConfig('featured_projects_limit', FEATURED_PROJECTS_LIMIT);
    return getActiveProjects(['featured' => true, 'limit' => $limit]);
}

/**
 * Registrar vista de proyecto (para analytics)
 */
function recordProjectView($projectId) {
    $db = getMarketplaceDB();
    $today = date('Y-m-d');
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_stats (project_id, stat_date, views_count, unique_visitors)
        VALUES (?, ?, 1, 1)
        ON DUPLICATE KEY UPDATE 
            views_count = views_count + 1
    ");
    
    return $stmt->execute([$projectId, $today]);
}

/**
 * Registrar click-through al sitio del proyecto
 */
function recordProjectClickThrough($projectId) {
    $db = getMarketplaceDB();
    $today = date('Y-m-d');
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_stats (project_id, stat_date, click_throughs)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE 
            click_throughs = click_throughs + 1
    ");
    
    return $stmt->execute([$projectId, $today]);
}

/**
 * Obtener estadísticas de un proyecto
 */
function getProjectStats($projectId, $days = 30) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("
        SELECT 
            stat_date,
            views_count,
            unique_visitors,
            click_throughs
        FROM tbl_marketplace_stats
        WHERE project_id = ? 
        AND stat_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ORDER BY stat_date DESC
    ");
    $stmt->execute([$projectId, $days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Crear nuevo proyecto
 */
function createProject($data) {
    $db = getMarketplaceDB();
    
    // Generar slug si no existe
    if (empty($data['slug'])) {
        $data['slug'] = generateSlug($data['name']);
    }
    
    // Verificar que el slug sea único
    $slugExists = $db->prepare("SELECT id FROM tbl_marketplace_projects WHERE slug = ?");
    $slugExists->execute([$data['slug']]);
    if ($slugExists->fetch()) {
        $data['slug'] .= '-' . uniqid();
    }
    
    $fields = [];
    $placeholders = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $fields[] = $key;
        $placeholders[] = '?';
        $values[] = $value;
    }
    
    $sql = "INSERT INTO tbl_marketplace_projects (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $db->prepare($sql);
    
    if ($stmt->execute($values)) {
        return $db->lastInsertId();
    }
    return false;
}

/**
 * Actualizar proyecto
 */
function updateProject($projectId, $data) {
    $db = getMarketplaceDB();
    
    $fields = [];
    $values = [];
    
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $projectId;
    
    $sql = "UPDATE tbl_marketplace_projects SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
}

/**
 * Eliminar proyecto
 */
function deleteProject($projectId) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("DELETE FROM tbl_marketplace_projects WHERE id = ?");
    return $stmt->execute([$projectId]);
}

/**
 * Obtener resumen del marketplace (para dashboard admin)
 */
function getMarketplaceSummary() {
    $db = getMarketplaceDB();
    
    $summary = [];
    
    // Total de proyectos
    $stmt = $db->query("SELECT COUNT(*) as total FROM tbl_marketplace_projects WHERE is_active = TRUE");
    $summary['total_projects'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Proyectos por estado
    $stmt = $db->query("
        SELECT status, COUNT(*) as count 
        FROM tbl_marketplace_projects 
        WHERE is_active = TRUE 
        GROUP BY status
    ");
    $summary['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total de financiamiento
    $stmt = $db->query("
        SELECT 
            SUM(funding_goal) as total_goal,
            SUM(funding_raised) as total_raised
        FROM tbl_marketplace_projects 
        WHERE is_active = TRUE
    ");
    $summary['funding'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vistas totales (últimos 30 días)
    $stmt = $db->query("
        SELECT 
            SUM(views_count) as total_views,
            SUM(click_throughs) as total_clicks
        FROM tbl_marketplace_stats
        WHERE stat_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $summary['analytics'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $summary;
}

/**
 * Validar estructura de datos JSON del proyecto
 */
function validateProjectData($data) {
    $errors = [];
    
    // Validar estructura básica
    if (!isset($data['project_info'])) {
        $errors[] = "Falta sección 'project_info'";
    }
    
    if (!isset($data['blockchain'])) {
        $errors[] = "Falta sección 'blockchain'";
    }
    
    if (!isset($data['links'])) {
        $errors[] = "Falta sección 'links'";
    }
    
    // Validar campos requeridos
    if (isset($data['project_info'])) {
        $required = ['name', 'category', 'status'];
        foreach ($required as $field) {
            if (empty($data['project_info'][$field])) {
                $errors[] = "Campo requerido 'project_info.$field' está vacío";
            }
        }
    }
    
    return $errors;
}
