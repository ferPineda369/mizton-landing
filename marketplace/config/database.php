<?php
/**
 * Configuración de Base de Datos - Marketplace Mizton
 * Usa la misma conexión que el panel principal
 */

// Incluir configuración del panel principal
require_once __DIR__ . '/../../../panel/app/config/database.php';

// La conexión ya está disponible en $conn desde el panel
// No es necesario crear una nueva conexión

// Función helper para obtener la conexión
function getMarketplaceDB() {
    global $conn;
    return $conn;
}
