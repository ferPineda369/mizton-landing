<?php
/**
 * API para Guardar/Actualizar Proyectos del Marketplace
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
$action = $_POST['action'] ?? 'edit';

try {
    // Validar campos requeridos
    $requiredFields = ['project_code', 'name', 'slug', 'category', 'status', 'short_description'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }

    // Preparar datos
    $projectCode = strtoupper(trim($_POST['project_code']));
    $name = trim($_POST['name']);
    $slug = strtolower(trim($_POST['slug']));
    $category = $_POST['category'];
    $status = $_POST['status'];
    $shortDescription = trim($_POST['short_description']);
    $description = trim($_POST['description'] ?? '');
    
    // URLs e imágenes
    $mainImageUrl = trim($_POST['main_image_url'] ?? '');
    $logoUrl = trim($_POST['logo_url'] ?? '');
    $websiteUrl = trim($_POST['website_url'] ?? '');
    
    // Datos financieros
    $tokenSymbol = trim($_POST['token_symbol'] ?? '');
    $tokenPriceUsd = !empty($_POST['token_price_usd']) ? floatval($_POST['token_price_usd']) : null;
    $fundingGoal = !empty($_POST['funding_goal']) ? floatval($_POST['funding_goal']) : null;
    $fundingRaised = !empty($_POST['funding_raised']) ? floatval($_POST['funding_raised']) : null;
    $fundingPercentage = !empty($_POST['funding_percentage']) ? floatval($_POST['funding_percentage']) : null;
    $holdersCount = !empty($_POST['holders_count']) ? intval($_POST['holders_count']) : null;
    $apyPercentage = !empty($_POST['apy_percentage']) ? floatval($_POST['apy_percentage']) : null;
    
    // Calcular porcentaje si no se proporcionó
    if ($fundingPercentage === null && $fundingGoal > 0 && $fundingRaised !== null) {
        $fundingPercentage = ($fundingRaised / $fundingGoal) * 100;
    }
    
    // Blockchain
    $blockchainNetwork = trim($_POST['blockchain_network'] ?? '');
    $contractAddress = trim($_POST['contract_address'] ?? '');
    
    // Sincronización
    $updateMethod = $_POST['update_method'] ?? 'manual';
    $updateFrequency = !empty($_POST['update_frequency']) ? intval($_POST['update_frequency']) : null;
    
    // Opciones de visualización
    $featured = isset($_POST['featured']) ? 1 : 0;
    $featuredOrder = intval($_POST['featured_order'] ?? 0);
    $displayOrder = intval($_POST['display_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validar código del proyecto (solo letras, números, guiones y guiones bajos)
    if (!preg_match('/^[A-Z0-9_-]+$/', $projectCode)) {
        throw new Exception('El código del proyecto solo puede contener letras mayúsculas, números, guiones y guiones bajos');
    }
    
    // Validar slug (solo letras minúsculas, números y guiones)
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        throw new Exception('El slug solo puede contener letras minúsculas, números y guiones');
    }

    if ($action === 'new') {
        // Verificar que no exista el código
        $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_marketplace_projects WHERE project_code = ?");
        $stmt->execute([$projectCode]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe un proyecto con ese código');
        }
        
        // Verificar que no exista el slug
        $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_marketplace_projects WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe un proyecto con ese slug');
        }
        
        // Insertar nuevo proyecto
        $sql = "INSERT INTO tbl_marketplace_projects (
            project_code, name, slug, category, status,
            short_description, description,
            main_image_url, logo_url, website_url,
            token_symbol, token_price_usd,
            funding_goal, funding_raised, funding_percentage,
            holders_count, apy_percentage,
            blockchain_network, contract_address,
            update_method, update_frequency,
            featured, featured_order, display_order, is_active,
            created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?,
            ?, ?,
            ?, ?, ?, ?,
            NOW(), NOW()
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $projectCode, $name, $slug, $category, $status,
            $shortDescription, $description,
            $mainImageUrl, $logoUrl, $websiteUrl,
            $tokenSymbol, $tokenPriceUsd,
            $fundingGoal, $fundingRaised, $fundingPercentage,
            $holdersCount, $apyPercentage,
            $blockchainNetwork, $contractAddress,
            $updateMethod, $updateFrequency,
            $featured, $featuredOrder, $displayOrder, $isActive
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto creado exitosamente',
            'redirect' => '/marketplace/admin/projects.php?success=created'
        ]);
        
    } else {
        // Actualizar proyecto existente
        $originalCode = $_POST['original_code'] ?? $projectCode;
        
        // Verificar que el proyecto existe
        $stmt = $db->prepare("SELECT id FROM tbl_marketplace_projects WHERE project_code = ?");
        $stmt->execute([$originalCode]);
        if (!$stmt->fetch()) {
            throw new Exception('Proyecto no encontrado');
        }
        
        // Verificar slug único (excepto el proyecto actual)
        $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_marketplace_projects WHERE slug = ? AND project_code != ?");
        $stmt->execute([$slug, $originalCode]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe otro proyecto con ese slug');
        }
        
        // Actualizar proyecto
        $sql = "UPDATE tbl_marketplace_projects SET
            name = ?, slug = ?, category = ?, status = ?,
            short_description = ?, description = ?,
            main_image_url = ?, logo_url = ?, website_url = ?,
            token_symbol = ?, token_price_usd = ?,
            funding_goal = ?, funding_raised = ?, funding_percentage = ?,
            holders_count = ?, apy_percentage = ?,
            blockchain_network = ?, contract_address = ?,
            update_method = ?, update_frequency = ?,
            featured = ?, featured_order = ?, display_order = ?, is_active = ?,
            updated_at = NOW()
        WHERE project_code = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name, $slug, $category, $status,
            $shortDescription, $description,
            $mainImageUrl, $logoUrl, $websiteUrl,
            $tokenSymbol, $tokenPriceUsd,
            $fundingGoal, $fundingRaised, $fundingPercentage,
            $holdersCount, $apyPercentage,
            $blockchainNetwork, $contractAddress,
            $updateMethod, $updateFrequency,
            $featured, $featuredOrder, $displayOrder, $isActive,
            $originalCode
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente',
            'redirect' => '/marketplace/admin/projects.php?success=updated'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
