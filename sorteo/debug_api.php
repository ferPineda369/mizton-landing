<?php
header('Content-Type: application/json');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => []
];

// Test 1: Verificar archivo de configuración
$debug['tests']['config_file'] = [
    'exists' => file_exists('config/database.php'),
    'readable' => is_readable('config/database.php')
];

// Test 2: Probar inclusión de configuración
try {
    require_once 'config/database.php';
    $debug['tests']['config_include'] = [
        'success' => true,
        'pdo_exists' => isset($pdo)
    ];
} catch (Exception $e) {
    $debug['tests']['config_include'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// Test 3: Probar función cleanExpiredReservations
try {
    cleanExpiredReservations($pdo);
    $debug['tests']['clean_expired'] = ['success' => true];
} catch (Exception $e) {
    $debug['tests']['clean_expired'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Test 4: Probar creación de tabla temp_blocks
try {
    $createBlockTableSql = "
    CREATE TABLE IF NOT EXISTS sorteo_temp_blocks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        number_value INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        INDEX idx_number_session (number_value, session_id),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createBlockTableSql);
    $debug['tests']['create_temp_blocks'] = ['success' => true];
} catch (Exception $e) {
    $debug['tests']['create_temp_blocks'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Test 5: Probar limpieza de bloqueos
try {
    $pdo->exec("DELETE FROM sorteo_temp_blocks WHERE expires_at < NOW()");
    $debug['tests']['clean_blocks'] = ['success' => true];
} catch (Exception $e) {
    $debug['tests']['clean_blocks'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Test 6: Probar consulta principal
try {
    $sql = "SELECT 
                sn.number_value,
                sn.status,
                sn.participant_name,
                sn.participant_email,
                sn.reserved_at,
                sn.confirmed_at,
                sn.reservation_expires_at,
                stb.session_id as blocked_by_session,
                stb.expires_at as block_expires_at
            FROM sorteo_numbers sn
            LEFT JOIN sorteo_temp_blocks stb ON sn.number_value = stb.number_value 
                AND stb.expires_at > NOW()
            ORDER BY sn.number_value ASC
            LIMIT 5";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $debug['tests']['main_query'] = [
        'success' => true,
        'count' => count($numbers),
        'sample' => array_slice($numbers, 0, 2)
    ];
} catch (Exception $e) {
    $debug['tests']['main_query'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Test 7: Verificar tablas existentes
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'sorteo_%'")->fetchAll(PDO::FETCH_COLUMN);
    $debug['tests']['tables'] = [
        'success' => true,
        'tables' => $tables
    ];
} catch (Exception $e) {
    $debug['tests']['tables'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
