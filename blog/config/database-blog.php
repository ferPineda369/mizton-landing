<?php
/**
 * Configuración de base de datos específica para el blog
 * Fallback si no se puede cargar database.php principal
 */

// Intentar cargar configuración principal primero
$main_db_loaded = false;
$possible_paths = [
    __DIR__ . '/../../database.php',
    dirname(dirname(__DIR__)) . '/database.php',
    dirname(__DIR__) . '/../database.php',
    '/usr/local/lsws/VH_mizton/html/database.php',
    '/usr/local/lsws/VH_mizton/html/landing/database.php'
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $main_db_loaded = true;
        break;
    }
}

// Si no se pudo cargar la configuración principal, usar configuración directa
if (!$main_db_loaded) {
    // Configuración directa de base de datos para el blog
    $db_config = [
        'host' => 'localhost',
        'dbname' => 'u337955665_mizton',  // Base de datos de producción
        'username' => 'u337955665_mizton',
        'password' => 'Mizton2024!',
        'charset' => 'utf8mb4'
    ];
    
    try {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
        $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Configurar zona horaria
        $pdo->exec("SET time_zone = '-06:00'");
        
    } catch (PDOException $e) {
        error_log("Blog DB Error: " . $e->getMessage());
        
        // Respuesta de error
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Database connection failed',
                'message' => 'Please try again later'
            ]);
            exit;
        }
        
        echo "<h2>Blog Database Error</h2><p>Unable to connect to database. Please try again later.</p>";
        exit;
    }
}

// Función para obtener la conexión (compatible con ambos sistemas)
if (!function_exists('getBlogDB')) {
    function getBlogDB() {
        global $pdo;
        return $pdo;
    }
}
?>
