<?php
/**
 * Bootstrap simplificado para Landing Page
 * Evita conflictos con el bootstrap del panel
 */

/**
 * Carga variables de entorno desde archivo .env
 */
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Ignorar comentarios
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remover comillas si existen
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }
        
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
    
    return true;
}

// Cargar variables de entorno
$envFile = __DIR__ . '/.env';
if (!loadEnvFile($envFile)) {
    error_log("Landing: No se pudo cargar archivo .env, usando valores por defecto");
}

// Configuración de base de datos desde variables de entorno
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? 'mizton_db',
    'username' => $_ENV['DB_USER'] ?? 'mizton_user',
    'password' => $_ENV['DB_PASS'] ?? 'Mizton2024!',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Configurar zona horaria desde variable de entorno
    $timezone = $_ENV['TIMEZONE'] ?? '-06:00';
    $pdo->exec("SET time_zone = '$timezone'");
    
} catch (PDOException $e) {
    error_log("Landing DB Error: " . $e->getMessage());
    
    // Respuesta JSON para APIs
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed',
            'message' => 'Please try again later'
        ]);
        exit;
    }
    
    // Respuesta HTML para páginas
    echo "<h2>Database Connection Error</h2><p>Service temporarily unavailable. Please try again later.</p>";
    exit;
}

// Constantes específicas para landing (evitar conflictos)
if (!defined('LANDING_VERSION')) {
    define('LANDING_VERSION', '1.0.0');
}

if (!defined('LANDING_DEBUG')) {
    define('LANDING_DEBUG', ($_ENV['DEBUG'] ?? 'false') === 'true');
}

if (!defined('DEFAULT_WHATSAPP')) {
    define('DEFAULT_WHATSAPP', $_ENV['DEFAULT_WHATSAPP'] ?? '5212226536090');
}
?>
