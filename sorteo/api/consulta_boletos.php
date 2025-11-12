<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Validar datos de entrada
    $phoneNumber = trim(filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    // Validaciones
    if (!$phoneNumber || !preg_match('/^[0-9]{10}$/', $phoneNumber)) {
        echo json_encode([
            'success' => false,
            'message' => 'El número celular debe tener exactamente 10 dígitos'
        ]);
        exit;
    }
    
    // Buscar boletos por número celular
    $sql = "SELECT 
                number_value,
                status,
                participant_name,
                reserved_at,
                confirmed_at,
                reservation_expires_at
            FROM sorteo_numbers 
            WHERE participant_movil = ? 
            AND status IN ('reserved', 'confirmed')
            ORDER BY number_value ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$phoneNumber]);
    $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($boletos)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron boletos asociados a este número celular'
        ]);
        exit;
    }
    
    // Procesar resultados
    $boletosFormateados = [];
    foreach ($boletos as $boleto) {
        $boletosFormateados[] = [
            'numero' => (int)$boleto['number_value'],
            'estado' => $boleto['status'],
            'participante' => $boleto['participant_name'],
            'fecha_reserva' => $boleto['reserved_at'] ? date('d/m/Y H:i', strtotime($boleto['reserved_at'])) : null,
            'fecha_confirmacion' => $boleto['confirmed_at'] ? date('d/m/Y H:i', strtotime($boleto['confirmed_at'])) : null,
            'expira_en' => $boleto['reservation_expires_at'] ? date('d/m/Y H:i', strtotime($boleto['reservation_expires_at'])) : null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Boletos encontrados',
        'data' => [
            'phoneNumber' => $phoneNumber,
            'totalBoletos' => count($boletosFormateados),
            'boletos' => $boletosFormateados
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en consulta_boletos.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
