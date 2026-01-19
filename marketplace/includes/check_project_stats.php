<?php
/**
 * Script temporal para verificar estadísticas del proyecto KIMEN_TOKEN
 */

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/marketplace-functions.php';

$db = getMarketplaceDB();

echo "=== Verificación de Estadísticas del Proyecto KIMEN_TOKEN ===\n\n";

// 1. Datos actuales en tbl_marketplace_projects
echo "1. DATOS EN tbl_marketplace_projects:\n";
$stmt = $db->prepare("
    SELECT 
        id,
        project_code,
        name,
        token_price_usd,
        funding_goal,
        funding_raised,
        funding_percentage,
        holders_count
    FROM tbl_marketplace_projects 
    WHERE project_code = 'KIMEN_TOKEN'
");
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if ($project) {
    echo "  - ID: {$project['id']}\n";
    echo "  - Nombre: {$project['name']}\n";
    echo "  - Precio Token: \${$project['token_price_usd']}\n";
    echo "  - Meta Financiamiento: \${$project['funding_goal']}\n";
    echo "  - Financiamiento Recaudado: \${$project['funding_raised']}\n";
    echo "  - Porcentaje Financiamiento: {$project['funding_percentage']}%\n";
    echo "  - Número de Inversores (holders_count): {$project['holders_count']}\n\n";
} else {
    echo "  ❌ Proyecto no encontrado\n\n";
    exit;
}

// 2. Datos REALES de inversores
echo "2. DATOS REALES DE INVERSORES:\n";
$stmt = $db->prepare("
    SELECT 
        COUNT(DISTINCT id) as total_investors,
        SUM(token_amount) as total_tokens,
        SUM(investment_usd) as total_investment_usd
    FROM tbl_marketplace_project_investors 
    WHERE project_id = ? AND is_active = 1
");
$stmt->execute([$project['id']]);
$realStats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "  - Total Inversores REALES: {$realStats['total_investors']}\n";
echo "  - Total Tokens Vendidos: {$realStats['total_tokens']}\n";
echo "  - Total Inversión USD: \${$realStats['total_investment_usd']}\n\n";

// 3. Cálculo correcto del porcentaje
$correctPercentage = 0;
if ($project['funding_goal'] > 0) {
    $correctPercentage = ($realStats['total_investment_usd'] / $project['funding_goal']) * 100;
}

echo "3. CÁLCULO CORRECTO:\n";
echo "  - Fórmula: (Total Inversión / Meta) * 100\n";
echo "  - Cálculo: (\${$realStats['total_investment_usd']} / \${$project['funding_goal']}) * 100\n";
echo "  - Porcentaje CORRECTO: " . number_format($correctPercentage, 2) . "%\n\n";

// 4. Comparación
echo "4. COMPARACIÓN:\n";
echo "  - Porcentaje en BD: {$project['funding_percentage']}% ❌\n";
echo "  - Porcentaje REAL: " . number_format($correctPercentage, 2) . "% ✅\n";
echo "  - Diferencia: " . number_format(abs($project['funding_percentage'] - $correctPercentage), 2) . "%\n\n";

echo "  - Inversores en BD: {$project['holders_count']} ❌\n";
echo "  - Inversores REALES: {$realStats['total_investors']} ✅\n";
echo "  - Diferencia: " . abs($project['holders_count'] - $realStats['total_investors']) . "\n\n";

// 5. Detalle de inversores
echo "5. DETALLE DE INVERSORES:\n";
$stmt = $db->prepare("
    SELECT 
        i.id,
        i.user_id,
        u.nameUser,
        i.token_amount,
        i.investment_usd,
        i.created_at
    FROM tbl_marketplace_project_investors i
    LEFT JOIN tbluser u ON i.user_id = u.idUser
    WHERE i.project_id = ? AND i.is_active = 1
    ORDER BY i.created_at DESC
");
$stmt->execute([$project['id']]);
$investors = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($investors as $index => $investor) {
    echo "  " . ($index + 1) . ". {$investor['nameUser']} (user_id: {$investor['user_id']})\n";
    echo "     - Tokens: {$investor['token_amount']}\n";
    echo "     - Inversión: \${$investor['investment_usd']}\n";
    echo "     - Fecha: {$investor['created_at']}\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
