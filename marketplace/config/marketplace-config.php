<?php
/**
 * Configuración del Marketplace Mizton
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir base de datos
require_once __DIR__ . '/database.php';

// Configuración general del marketplace
define('MARKETPLACE_NAME', 'Mizton Marketplace');
define('MARKETPLACE_TAGLINE', 'Proyectos Tokenizados de Activos Reales');
define('MARKETPLACE_URL', 'https://mizton.cat/marketplace');

// Rutas de archivos
define('MARKETPLACE_ROOT', __DIR__ . '/..');
define('MARKETPLACE_UPLOADS', MARKETPLACE_ROOT . '/uploads');
define('MARKETPLACE_UPLOADS_URL', '/marketplace/uploads');

// Configuración de sincronización
define('DEFAULT_SYNC_FREQUENCY', 5); // minutos
define('SYNC_TIMEOUT', 30); // segundos
define('MAX_SYNC_RETRIES', 3);

// Configuración de visualización
define('PROJECTS_PER_PAGE', 12);
define('FEATURED_PROJECTS_LIMIT', 6);

// Categorías de proyectos (sincronizado con BD)
$MARKETPLACE_CATEGORIES = [
    'inmobiliario' => ['name' => 'Inmobiliario', 'icon' => 'bi-building', 'color' => '#3498db'],
    'energia' => ['name' => 'Energía', 'icon' => 'bi-lightning-charge', 'color' => '#f39c12'],
    'editorial' => ['name' => 'Editorial', 'icon' => 'bi-book', 'color' => '#9b59b6'],
    'arte' => ['name' => 'Arte', 'icon' => 'bi-palette', 'color' => '#e74c3c'],
    'musical' => ['name' => 'Musical', 'icon' => 'bi-music-note-beamed', 'color' => '#1abc9c'],
    'cinematografia' => ['name' => 'Cinematografía', 'icon' => 'bi-film', 'color' => '#34495e'],
    'deportivo' => ['name' => 'Deportivo', 'icon' => 'bi-trophy', 'color' => '#27ae60'],
    'agropecuario' => ['name' => 'Agropecuario', 'icon' => 'bi-tree', 'color' => '#16a085'],
    'industrial' => ['name' => 'Industrial', 'icon' => 'bi-gear', 'color' => '#7f8c8d'],
    'tecnologia' => ['name' => 'Tecnología', 'icon' => 'bi-cpu', 'color' => '#3498db'],
    'minero' => ['name' => 'Minero', 'icon' => 'bi-gem', 'color' => '#95a5a6'],
    'farmaceutico' => ['name' => 'Farmacéutico', 'icon' => 'bi-capsule', 'color' => '#e67e22'],
    'gubernamental' => ['name' => 'Gubernamental', 'icon' => 'bi-bank', 'color' => '#2c3e50'],
    'otro' => ['name' => 'Otro', 'icon' => 'bi-grid', 'color' => '#95a5a6']
];

// Estados de proyectos
$PROJECT_STATUSES = [
    'desarrollo' => ['label' => 'En Desarrollo', 'badge' => 'secondary', 'icon' => 'bi-gear'],
    'preventa' => ['label' => 'Preventa', 'badge' => 'warning', 'icon' => 'bi-clock'],
    'activo' => ['label' => 'Activo', 'badge' => 'success', 'icon' => 'bi-check-circle'],
    'financiado' => ['label' => 'Financiado', 'badge' => 'info', 'icon' => 'bi-cash-stack'],
    'completado' => ['label' => 'Completado', 'badge' => 'primary', 'icon' => 'bi-flag'],
    'pausado' => ['label' => 'Pausado', 'badge' => 'warning', 'icon' => 'bi-pause-circle'],
    'cerrado' => ['label' => 'Cerrado', 'badge' => 'dark', 'icon' => 'bi-x-circle']
];

// Métodos de actualización
$UPDATE_METHODS = [
    'manual' => 'Manual',
    'api_pull' => 'API Pull',
    'webhook' => 'Webhook',
    'blockchain' => 'Blockchain'
];

// Redes blockchain soportadas
$BLOCKCHAIN_NETWORKS = [
    'BSC' => ['name' => 'Binance Smart Chain', 'explorer' => 'https://bscscan.com'],
    'ETH' => ['name' => 'Ethereum', 'explorer' => 'https://etherscan.io'],
    'POLYGON' => ['name' => 'Polygon', 'explorer' => 'https://polygonscan.com'],
    'ARBITRUM' => ['name' => 'Arbitrum', 'explorer' => 'https://arbiscan.io'],
    'OPTIMISM' => ['name' => 'Optimism', 'explorer' => 'https://optimistic.etherscan.io']
];

/**
 * Función para verificar si el usuario es administrador
 */
function isMarketplaceAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
}

/**
 * Función para verificar si el marketplace está activo
 */
function isMarketplaceEnabled() {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("SELECT config_value FROM tbl_marketplace_config WHERE config_key = 'marketplace_enabled'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['config_value'] === 'true';
}

/**
 * Función para obtener configuración del marketplace
 */
function getMarketplaceConfig($key, $default = null) {
    $db = getMarketplaceDB();
    $stmt = $db->prepare("SELECT config_value, config_type FROM tbl_marketplace_config WHERE config_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return $default;
    }
    
    $value = $result['config_value'];
    
    // Convertir según tipo
    switch ($result['config_type']) {
        case 'boolean':
            return $value === 'true';
        case 'number':
            return is_numeric($value) ? (float)$value : $default;
        case 'json':
            return json_decode($value, true);
        default:
            return $value;
    }
}

/**
 * Función para establecer configuración del marketplace
 */
function setMarketplaceConfig($key, $value, $type = 'string') {
    $db = getMarketplaceDB();
    
    // Convertir valor según tipo
    if ($type === 'boolean') {
        $value = $value ? 'true' : 'false';
    } elseif ($type === 'json') {
        $value = json_encode($value);
    } else {
        $value = (string)$value;
    }
    
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_config (config_key, config_value, config_type, updated_by) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            config_value = VALUES(config_value),
            config_type = VALUES(config_type),
            updated_by = VALUES(updated_by)
    ");
    
    return $stmt->execute([$key, $value, $type, $_SESSION['idUser'] ?? null]);
}

/**
 * Función para formatear números grandes
 */
function formatLargeNumber($number) {
    if ($number >= 1000000000) {
        return number_format($number / 1000000000, 2) . 'B';
    } elseif ($number >= 1000000) {
        return number_format($number / 1000000, 2) . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 2) . 'K';
    }
    return number_format($number, 2);
}

/**
 * Función para formatear moneda
 */
function formatCurrency($amount, $decimals = 2) {
    return '$' . number_format($amount, $decimals);
}

/**
 * Función para formatear porcentaje
 */
function formatPercentage($percentage, $decimals = 2) {
    return number_format($percentage, $decimals) . '%';
}

/**
 * Función para generar slug desde texto
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Función para validar URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Función para sanitizar entrada
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
