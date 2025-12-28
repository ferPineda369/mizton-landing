<?php
/**
 * Cron Job: Sincronización automática de proyectos
 * Ejecutar cada 5 minutos (o según configuración)
 * 
 * Configurar en crontab:
 * */5 * * * * /usr/bin/php /path/to/marketplace/cron/sync-projects.php >> /var/log/marketplace-sync.log 2>&1
 */

// Solo permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/sync-functions.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando sincronización de proyectos...\n";

try {
    $results = syncAllProjects();
    
    $total = count($results);
    $success = 0;
    $failed = 0;
    $skipped = 0;
    
    foreach ($results as $projectCode => $result) {
        if (isset($result['skipped']) && $result['skipped']) {
            $skipped++;
            echo "  ⏭️  $projectCode: Omitido - {$result['reason']}\n";
        } elseif ($result['success']) {
            $success++;
            echo "  ✅ $projectCode: Sincronizado exitosamente\n";
        } else {
            $failed++;
            echo "  ❌ $projectCode: Error - {$result['error']}\n";
        }
    }
    
    echo "\n";
    echo "Resumen:\n";
    echo "  Total: $total proyectos\n";
    echo "  Exitosos: $success\n";
    echo "  Fallidos: $failed\n";
    echo "  Omitidos: $skipped\n";
    echo "\n";
    
    // Si hay fallos, intentar reintentar
    if ($failed > 0) {
        echo "Reintentando sincronizaciones fallidas...\n";
        $retryResults = retrySyncFailures();
        
        $retrySuccess = 0;
        foreach ($retryResults as $projectCode => $result) {
            if ($result['success']) {
                $retrySuccess++;
                echo "  ✅ $projectCode: Reintento exitoso\n";
            }
        }
        
        echo "Reintentos exitosos: $retrySuccess\n";
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Sincronización completada\n";
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
