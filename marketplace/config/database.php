<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Usa la misma conexión que el panel principal
 */

// Detectar entorno y usar ruta correcta al panel
if (file_exists(__DIR__ . '/../../../panel/app/config/database.php')) {
    // Desarrollo: d:\xampp\htdocs\landing\marketplace\config\
    require_once __DIR__ . '/../../../panel/app/config/database.php';
} elseif (file_exists('/usr/local/lsws/VH_mizton/html/app/config/database.php')) {
    // Producción: /usr/local/lsws/Example/html/marketplace/config/
    require_once '/usr/local/lsws/VH_mizton/html/app/config/database.php';
} else {
    die('Error: No se pudo encontrar el archivo de configuración de la base de datos del panel.');
}

// La conexión ya está disponible en $conn desde el panel
// No es necesario crear una nueva conexión

// Función helper para obtener la conexión
function getMarketplaceDB() {
    global $conn;
    return $conn;
}
