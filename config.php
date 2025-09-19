<?php
/**
 * Configuración para Landing Page
 * Detecta el entorno y configura las URLs apropiadas
 */

// Detectar el entorno basado en el dominio
$currentDomain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

if (strpos($currentDomain, 'mizton.cat') !== false) {
    // Entorno de producción VPS
    $panelUrl = 'https://panel.mizton.cat';
    $registerUrl = $panelUrl . '/register.php';
    $environment = 'production';
} elseif (strpos($currentDomain, 'localhost') !== false || strpos($currentDomain, '127.0.0.1') !== false) {
    // Entorno local - intentar cargar configuración del panel si existe
    if (file_exists('../panel/config/environments.php')) {
        try {
            require_once '../panel/config/environments.php';
            $config = EnvironmentConfig::getConfig();
            $panelUrl = $config['panel_url'];
            $registerUrl = $panelUrl . '/register.php';
            $environment = $config['environment'];
        } catch (Exception $e) {
            // Fallback local
            $panelUrl = 'http://localhost/panel';
            $registerUrl = $panelUrl . '/register.php';
            $environment = 'local';
            error_log("Error obteniendo configuración: " . $e->getMessage());
        }
    } else {
        // Fallback local sin archivo de configuración
        $panelUrl = 'http://localhost/panel';
        $registerUrl = $panelUrl . '/register.php';
        $environment = 'local';
    }
} else {
    // Fallback para otros entornos
    $panelUrl = 'http://localhost/panel';
    $registerUrl = $panelUrl . '/register.php';
    $environment = 'unknown';
}

// Configuración para JavaScript
$landingConfig = [
    'panel_url' => $panelUrl,
    'register_url' => $registerUrl,
    'environment' => $environment,
    'whatsapp_number' => '2215695942',
    'referido' => isset($_SESSION['referido']) ? $_SESSION['referido'] : null
];
?>
<script>
// Configuración global para la landing page
window.MIZTON_CONFIG = <?php echo json_encode($landingConfig); ?>;
</script>
