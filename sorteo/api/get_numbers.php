<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de configuración de base de datos',
        'error' => $e->getMessage()
    ]);
    exit;
}

try {
    // Limpiar reservas expiradas antes de obtener los números
    cleanExpiredReservations($pdo);
    
    // Crear tabla de bloqueos temporales si no existe
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
    
    // Limpiar bloqueos temporales expirados
    $pdo->exec("DELETE FROM sorteo_temp_blocks WHERE expires_at < NOW()");
    
    // Obtener todos los números con su estado actual y bloqueos temporales
    $sql = "SELECT 
                sn.number_value,
                sn.status,
                sn.participant_name,
                sn.participant_movil,
                sn.reserved_at,
                sn.confirmed_at,
                sn.reservation_expires_at,
                stb.session_id as blocked_by_session,
                stb.expires_at as block_expires_at
            FROM sorteo_numbers sn
            LEFT JOIN sorteo_temp_blocks stb ON sn.number_value = stb.number_value 
                AND stb.expires_at > NOW()
            ORDER BY sn.number_value ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar los datos para el frontend
    $processedNumbers = [];
    $currentSessionId = session_id();
    
    foreach ($numbers as $number) {
        // Determinar el estado efectivo del número
        $effectiveStatus = $number['status'];
        $isBlockedByOther = false;
        
        if ($number['blocked_by_session'] && $number['blocked_by_session'] !== $currentSessionId) {
            $isBlockedByOther = true;
            if ($effectiveStatus === 'available') {
                $effectiveStatus = 'blocked';
            }
        }
        
        $processedNumbers[] = [
            'number_value' => (int)$number['number_value'],
            'status' => $effectiveStatus,
            'participant_name' => $number['participant_name'],
            'participant_movil' => $number['participant_movil'],
            'reserved_at' => $number['reserved_at'],
            'confirmed_at' => $number['confirmed_at'],
            'reservation_expires_at' => $number['reservation_expires_at'],
            'is_blocked_by_other' => $isBlockedByOther,
            'block_expires_at' => $number['block_expires_at']
        ];
    }
    
    // Obtener estadísticas
    $stats = [
        'total' => 100,
        'available' => 0,
        'reserved' => 0,
        'confirmed' => 0
    ];
    
    foreach ($processedNumbers as $number) {
        $stats[$number['status']]++;
    }
    
    echo json_encode([
        'success' => true,
        'numbers' => $processedNumbers,
        'stats' => $stats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_numbers.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?>
