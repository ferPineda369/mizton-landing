<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Usa la misma conexión que el panel principal
 */

// Detectar entorno y usar ruta correcta al panel
$panelConfigPaths = [
    __DIR__ . '/../../../panel/app/config/database.php', // Desarrollo (estructura antigua)
    __DIR__ . '/../../../panel/config/database.php', // Desarrollo (estructura nueva)
    '/usr/local/lsws/VH_mizton/html/config/database.php', // Producción (ruta correcta)
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
    die('Error: No se pudo encontrar database.php del panel. Rutas intentadas: ' . implode(', ', $panelConfigPaths));
}

// La conexión ya está disponible en $conn desde el panel
// No es necesario crear una nueva conexión

// Función helper para obtener la conexión
function getMarketplaceDB() {
    global $conn;
    return $conn;
}
