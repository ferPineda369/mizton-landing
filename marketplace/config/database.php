<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Conexión independiente usando credenciales del landing
 */

// Evitar redefinición si ya existe la conexión
if (isset($GLOBALS['marketplace_pdo'])) {
    function getMarketplaceDB() {
        return $GLOBALS['marketplace_pdo'];
    }
    return;
}

// Cargar variables de entorno del landing
$envPaths = [
    __DIR__ . '/../../.env', // Desarrollo: landing/.env
    '/usr/local/lsws/Example/html/.env', // Producción
];

$envLoaded = false;
foreach ($envPaths as $envPath) {
    if (file_exists($envPath)) {
        $envContent = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envContent as $line) {
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
        $envLoaded = true;
        break;
    }
}

// Configuración de credenciales
$db_config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_NAME'] ?? '',
    'username' => $_ENV['DB_USER'] ?? '',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4'
];

// Validar credenciales
if (empty($db_config['dbname']) || empty($db_config['username'])) {
    die('Error: Credenciales de base de datos no configuradas. Verifica el archivo .env del landing.');
}

// Crear conexión PDO
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $GLOBALS['marketplace_pdo'] = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    
} catch (PDOException $e) {
    error_log("Marketplace DB Error: " . $e->getMessage());
    die('Error de conexión a la base de datos del Marketplace.');
}

// Función helper para obtener la conexión
function getMarketplaceDB() {
    return $GLOBALS['marketplace_pdo'];
}
