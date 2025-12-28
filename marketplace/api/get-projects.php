<?php
/**
 * API: Obtener lista de proyectos del marketplace
 * GET /marketplace/api/get-projects.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';

try {
    // Obtener parámetros de filtrado
    $filters = [];
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $filters['category'] = sanitizeInput($_GET['category']);
    }
    
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = sanitizeInput($_GET['status']);
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = sanitizeInput($_GET['search']);
    }
    
    if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
        $filters['featured'] = true;
    }
    
    if (isset($_GET['order_by'])) {
        $filters['order_by'] = sanitizeInput($_GET['order_by']);
    }
    
    // Paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? min(50, max(1, intval($_GET['per_page']))) : PROJECTS_PER_PAGE;
    
    $filters['limit'] = $perPage;
    $filters['offset'] = ($page - 1) * $perPage;
    
    // Obtener proyectos
    $projects = getActiveProjects($filters);
    
    // Contar total (sin paginación)
    unset($filters['limit']);
    unset($filters['offset']);
    $totalProjects = count(getActiveProjects($filters));
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'projects' => $projects,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_projects' => $totalProjects,
            'total_pages' => ceil($totalProjects / $perPage)
        ],
        'filters_applied' => array_keys($filters)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener proyectos',
        'message' => $e->getMessage()
    ]);
}
