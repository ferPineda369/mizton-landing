<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Limpiar reservas expiradas antes de obtener los números
    cleanExpiredReservations($pdo);
    
    // Obtener todos los números con su estado actual
    $sql = "SELECT 
                number_value,
                status,
                participant_name,
                participant_email,
                reserved_at,
                confirmed_at,
                reservation_expires_at
            FROM sorteo_numbers 
            ORDER BY number_value ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar los datos para el frontend
    $processedNumbers = [];
    foreach ($numbers as $number) {
        $processedNumbers[] = [
            'number_value' => (int)$number['number_value'],
            'status' => $number['status'],
            'participant_name' => $number['participant_name'],
            'participant_email' => $number['participant_email'],
            'reserved_at' => $number['reserved_at'],
            'confirmed_at' => $number['confirmed_at'],
            'reservation_expires_at' => $number['reservation_expires_at']
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
