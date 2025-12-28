<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Usa la misma conexión que el panel principal
 */

// Paso 1: Cargar config.php del panel SOLO si no está cargado
// Verificar si APP_ENV ya está definido (indica que config.php ya se cargó)
if (!defined('APP_ENV')) {
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
}

// Paso 2: Cargar database.php del panel SOLO si $pdo no existe
if (!isset($pdo)) {
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
}

// Verificar que $pdo esté disponible
if (!isset($pdo)) {
    die('Error: La conexión PDO no está disponible. Verifica la configuración del panel.');
}

// Función helper para obtener la conexión (retorna PDO)
function getMarketplaceDB() {
    global $pdo;
    return $pdo;
}
