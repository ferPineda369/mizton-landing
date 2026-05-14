<?php
/**
 * API pública — Stats de un proyecto tokenizado
 * 
 * Endpoint:  /marketplace/api/project-stats.php?slug=kimen
 * Método:    GET
 * Cache:     60 segundos
 * CORS:      Habilitado (para landings con dominio externo)
 * 
 * Respuesta:
 * {
 *   "slug": "kimen",
 *   "name": "KIMEN",
 *   "tokens_sold": 34,
 *   "total_raised_usdt": 850,
 *   "total_supply": 4800,
 *   "token_price": 25,
 *   "percent_sold": 0.71,
 *   "buyers_count": 2,
 *   "sale_active": true,
 *   "updated_at": "2026-05-14T00:20:40Z"
 * }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Cache-Control: public, max-age=60');

// Validar slug
$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['slug'])) : '';
if (empty($slug)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid slug parameter']);
    exit;
}

// Conexión a BD
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getMarketplaceDB();
    $chainDb = 'mizton_chain';

    // Datos del proyecto
    $stmt = $pdo->prepare("
        SELECT p.slug, p.name, p.token_price, p.contract
        FROM {$chainDb}.projects p
        WHERE p.slug = ? AND p.active = 1
    ");
    $stmt->execute([$slug]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(['error' => 'Project not found']);
        exit;
    }

    // Tokens vendidos y recaudación
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(token_amount), 0) AS tokens_sold_raw,
            COALESCE(SUM(cost_usdt), 0) AS raised_raw,
            COUNT(DISTINCT buyer) AS buyers_count
        FROM {$chainDb}.purchases
        WHERE project_id = (SELECT id FROM {$chainDb}.projects WHERE slug = ?)
    ");
    $stmt->execute([$slug]);
    $sales = $stmt->fetch(PDO::FETCH_ASSOC);

    // Último sync
    $stmt = $pdo->prepare("
        SELECT ss.updated_at
        FROM {$chainDb}.sync_state ss
        JOIN {$chainDb}.projects p ON p.id = ss.project_id
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    $sync = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calcular valores (tokens y USDT vienen en wei — 18 decimales)
    $tokensSold = floatval(bcdiv($sales['tokens_sold_raw'] ?: '0', bcpow('10', '18'), 2));
    $totalRaised = floatval(bcdiv($sales['raised_raw'] ?: '0', bcpow('10', '18'), 2));
    $tokenPrice = floatval(bcdiv($project['token_price'] ?: '25000000000000000000', bcpow('10', '18'), 2));

    // Total supply según proyecto (configuración manual por ahora)
    $supplyMap = [
        'kimen' => 4800,
        'dmx'   => 100000,
    ];
    $totalSupply = $supplyMap[$slug] ?? 0;
    $percentSold = $totalSupply > 0 ? round(($tokensSold / $totalSupply) * 100, 2) : 0;

    echo json_encode([
        'slug'              => $project['slug'],
        'name'              => $project['name'],
        'tokens_sold'       => $tokensSold,
        'total_raised_usdt' => $totalRaised,
        'total_supply'      => $totalSupply,
        'token_price'       => $tokenPrice,
        'percent_sold'      => $percentSold,
        'buyers_count'      => (int)$sales['buyers_count'],
        'contract'          => $project['contract'],
        'updated_at'        => $sync['updated_at'] ?? null
    ]);

} catch (PDOException $e) {
    error_log("project-stats.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
