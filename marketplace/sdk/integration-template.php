<?php
/**
 * SDK de Integración - Mizton Marketplace
 * Template para que proyectos externos expongan sus datos
 * 
 * INSTRUCCIONES:
 * 1. Copiar este archivo a tu proyecto
 * 2. Configurar las constantes de conexión a BD
 * 3. Implementar las funciones de obtención de datos
 * 4. Exponer este endpoint en tu servidor
 * 5. Registrar la URL en Mizton Marketplace Admin
 */

// ============================================================================
// CONFIGURACIÓN
// ============================================================================

// Configurar según tu proyecto
define('PROJECT_CODE', 'LIBRO1'); // Código único del proyecto
define('API_SECRET', 'tu-secret-key-aqui'); // Para validar webhooks

// Conexión a tu base de datos (ajustar según tu proyecto)
// require_once 'config/database.php';

// ============================================================================
// ENDPOINT PRINCIPAL
// ============================================================================

header('Content-Type: application/json');

// Validar API Key si es necesario
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
// if ($apiKey !== 'tu-api-key') {
//     http_response_code(401);
//     echo json_encode(['error' => 'Unauthorized']);
//     exit;
// }

try {
    $data = getProjectData();
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// ============================================================================
// FUNCIONES DE OBTENCIÓN DE DATOS
// ============================================================================

/**
 * Función principal que retorna todos los datos del proyecto
 * en el formato estándar de Mizton Marketplace
 */
function getProjectData() {
    return [
        'project_info' => getProjectInfo(),
        'blockchain' => getBlockchainData(),
        'financials' => getFinancialData(),
        'participation' => getParticipationData(),
        'milestones' => getMilestones(),
        'links' => getProjectLinks(),
        'last_updated' => date('c') // ISO 8601
    ];
}

/**
 * Información básica del proyecto
 */
function getProjectInfo() {
    return [
        'name' => 'Nombre del Proyecto',
        'category' => 'editorial', // Ver categorías disponibles en documentación
        'description' => 'Descripción completa del proyecto...',
        'short_description' => 'Descripción corta para cards',
        'logo' => 'https://tu-proyecto.com/logo.png',
        'main_image' => 'https://tu-proyecto.com/hero.jpg',
        'status' => 'activo' // desarrollo, preventa, activo, financiado, completado, pausado, cerrado
    ];
}

/**
 * Datos de blockchain y token
 */
function getBlockchainData() {
    // Ejemplo: Obtener datos del smart contract
    // $contract = new Web3Contract(...);
    // $totalSupply = $contract->totalSupply();
    
    return [
        'contract_address' => '0x...', // Dirección del contrato
        'network' => 'BSC', // BSC, ETH, POLYGON, etc.
        'token_symbol' => 'BOOK',
        'total_supply' => 100000,
        'circulating_supply' => 50000,
        'token_price_usd' => 1.00,
        'market_cap' => 50000 // circulating_supply * token_price_usd
    ];
}

/**
 * Datos financieros del proyecto
 */
function getFinancialData() {
    // Ejemplo: Obtener de base de datos
    // $db = getDB();
    // $stmt = $db->query("SELECT SUM(amount) as raised FROM investments");
    // $raised = $stmt->fetch()['raised'];
    
    return [
        'funding_goal' => 100000,
        'raised' => 50000,
        'funding_percentage' => 50.0, // (raised / funding_goal) * 100
        'apy_staking' => 8.5, // APY de staking si aplica
        'apy_farming' => 0.0, // APY de farming si aplica
        'roi_projected' => 300, // ROI proyectado en %
        'total_value_locked' => 25000, // TVL si aplica
        'dividends_distributed' => 5000 // Dividendos ya distribuidos
    ];
}

/**
 * Datos de participación
 */
function getParticipationData() {
    // Ejemplo: Contar holders del contrato
    // $holders = $contract->getHoldersCount();
    
    return [
        'holders_count' => 250,
        'min_investment' => 100, // Inversión mínima en USD
        'max_investment' => 10000, // Inversión máxima en USD (null si no hay)
        'tokens_available' => 50000, // Tokens aún disponibles para compra
        'presale_start' => '2025-01-01', // Fecha inicio preventa
        'presale_end' => '2025-03-31' // Fecha fin preventa
    ];
}

/**
 * Milestones/Hitos del proyecto
 */
function getMilestones() {
    return [
        [
            'name' => 'Financiamiento',
            'description' => 'Alcanzar meta de financiamiento',
            'status' => 'in_progress', // pending, in_progress, completed, cancelled
            'percentage' => 50,
            'target_date' => '2025-03-31',
            'completed_date' => null
        ],
        [
            'name' => 'Desarrollo',
            'description' => 'Completar desarrollo del proyecto',
            'status' => 'pending',
            'percentage' => 0,
            'target_date' => '2025-06-30',
            'completed_date' => null
        ],
        [
            'name' => 'Lanzamiento',
            'description' => 'Lanzamiento oficial',
            'status' => 'pending',
            'percentage' => 0,
            'target_date' => '2025-09-30',
            'completed_date' => null
        ]
    ];
}

/**
 * Enlaces del proyecto
 */
function getProjectLinks() {
    return [
        'website' => 'https://tu-proyecto.com',
        'dashboard' => 'https://tu-proyecto.com/dashboard',
        'whitepaper' => 'https://tu-proyecto.com/docs/whitepaper.pdf',
        'pitch_deck' => 'https://tu-proyecto.com/docs/pitch.pdf',
        'twitter' => 'https://twitter.com/tu-proyecto',
        'telegram' => 'https://t.me/tu-proyecto',
        'discord' => 'https://discord.gg/tu-proyecto',
        'block_explorer' => 'https://bscscan.com/address/0x...'
    ];
}

// ============================================================================
// FUNCIÓN PARA ENVIAR WEBHOOK A MIZTON (OPCIONAL)
// ============================================================================

/**
 * Enviar actualización a Mizton Marketplace vía webhook
 * Llamar esta función cuando haya cambios importantes en el proyecto
 */
function sendWebhookToMizton() {
    $data = getProjectData();
    $data['project_code'] = PROJECT_CODE;
    $data['event_type'] = 'update';
    
    $payload = json_encode($data);
    $signature = hash_hmac('sha256', $payload, API_SECRET);
    
    $ch = curl_init('https://mizton.cat/marketplace/api/webhook-receiver.php');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Signature: ' . $signature
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $httpCode === 200,
        'response' => json_decode($response, true)
    ];
}

// ============================================================================
// EJEMPLO DE USO DEL WEBHOOK
// ============================================================================

// Llamar cuando se complete una inversión
// function onInvestmentCompleted($amount) {
//     // ... tu lógica ...
//     sendWebhookToMizton();
// }

// Llamar cuando se actualice un milestone
// function onMilestoneUpdated($milestoneId) {
//     // ... tu lógica ...
//     sendWebhookToMizton();
// }
