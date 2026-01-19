<?php
/**
 * Script para actualizar estadísticas del proyecto basándose en datos reales
 * Calcula funding_percentage y holders_count desde tbl_marketplace_project_investors
 */

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/marketplace-functions.php';

function updateProjectStats($projectId) {
    $db = getMarketplaceDB();
    
    // Obtener datos reales de inversores
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT id) as total_investors,
            SUM(token_amount) as total_tokens,
            SUM(investment_usd) as total_investment_usd
        FROM tbl_marketplace_project_investors 
        WHERE project_id = ? AND is_active = 1
    ");
    $stmt->execute([$projectId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener funding_goal del proyecto
    $stmt = $db->prepare("SELECT funding_goal FROM tbl_marketplace_projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular porcentaje correcto
    $fundingPercentage = 0;
    if ($project['funding_goal'] > 0) {
        $fundingPercentage = ($stats['total_investment_usd'] / $project['funding_goal']) * 100;
    }
    
    // Actualizar proyecto
    $stmt = $db->prepare("
        UPDATE tbl_marketplace_projects 
        SET 
            funding_raised = ?,
            funding_percentage = ?,
            holders_count = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $stats['total_investment_usd'],
        round($fundingPercentage, 2),
        $stats['total_investors'],
        $projectId
    ]);
    
    return [
        'total_investors' => $stats['total_investors'],
        'total_investment_usd' => $stats['total_investment_usd'],
        'funding_percentage' => round($fundingPercentage, 2)
    ];
}

// Si se ejecuta directamente
if (php_sapi_name() === 'cli' || basename($_SERVER['PHP_SELF']) === 'update_project_stats.php') {
    $db = getMarketplaceDB();
    
    // Obtener proyecto KIMEN_TOKEN
    $stmt = $db->prepare("SELECT id, project_code, name FROM tbl_marketplace_projects WHERE project_code = 'KIMEN_TOKEN'");
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        echo "Actualizando estadísticas del proyecto: {$project['name']} ({$project['project_code']})\n";
        $result = updateProjectStats($project['id']);
        echo "✅ Actualizado:\n";
        echo "  - Inversores: {$result['total_investors']}\n";
        echo "  - Inversión Total: \${$result['total_investment_usd']}\n";
        echo "  - Porcentaje Financiamiento: {$result['funding_percentage']}%\n";
    } else {
        echo "❌ Proyecto KIMEN_TOKEN no encontrado\n";
    }
}
