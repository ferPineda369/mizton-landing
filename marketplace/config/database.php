<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Usa la misma conexión que el panel principal
 */

// Paso 1: Cargar config.php del panel (define APP_ENV y carga .env)
$panelConfigPaths = [
    __DIR__ . '/../../../panel/config/config.php', // Desarrollo
    '/usr/local/lsws/VH_mizton/html/config/config.php', // Producción
];

$configLoaded = false;
foreach ($panelConfigPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $configLoaded = true;
        break;
    }
}

if (!$configLoaded) {
    die('Error: No se pudo encontrar config.php del panel.');
}

// Paso 2: Cargar database.php del panel (crea conexión PDO)
$panelDatabasePaths = [
    __DIR__ . '/../../../panel/config/database.php', // Desarrollo
    '/usr/local/lsws/VH_mizton/html/config/database.php', // Producción
];

$databaseLoaded = false;
foreach ($panelDatabasePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $databaseLoaded = true;
        break;
    }
}

if (!$databaseLoaded) {
    die('Error: No se pudo encontrar database.php del panel.');
}

// La conexión ya está disponible en $pdo desde el panel
// Función helper para obtener la conexión (retorna PDO)
function getMarketplaceDB() {
    global $pdo;
    return $pdo;
}
