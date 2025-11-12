<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos
    $input = json_decode(file_get_contents('php://input'), true);
    $number = filter_var($input['number'] ?? null, FILTER_VALIDATE_INT);
    $adminKey = $input['admin_key'] ?? '';
    
    // Validar clave de administrador (cambiar por una clave segura)
    if ($adminKey !== 'mizton_sorteo_2025') {
        echo json_encode([
            'success' => false,
            'message' => 'Acceso no autorizado'
        ]);
        exit;
    }
    
    if (!$number || $number < 1 || $number > 100) {
        echo json_encode([
            'success' => false,
            'message' => 'Número inválido'
        ]);
        exit;
    }
    
    // Verificar estado actual del número
    $checkSql = "SELECT * FROM sorteo_numbers WHERE number_value = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$number]);
    $currentNumber = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentNumber) {
        echo json_encode([
            'success' => false,
            'message' => 'Número no encontrado'
        ]);
        exit;
    }
    
    if ($currentNumber['status'] !== 'reserved') {
        echo json_encode([
            'success' => false,
            'message' => 'El número no está en estado reservado'
        ]);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Confirmar el pago
        $confirmSql = "UPDATE sorteo_numbers 
                       SET status = 'confirmed',
                           confirmed_at = NOW(),
                           reservation_expires_at = NULL
                       WHERE number_value = ? AND status = 'reserved'";
        
        $confirmStmt = $pdo->prepare($confirmSql);
        $result = $confirmStmt->execute([$number]);
        
        if (!$result || $confirmStmt->rowCount() === 0) {
            throw new Exception('No se pudo confirmar el pago');
        }
        
        // Registrar en el log
        $logSql = "INSERT INTO sorteo_transactions 
                   (number_value, participant_name, participant_email, action, ip_address, user_agent) 
                   VALUES (?, ?, ?, 'confirmed', ?, ?)";
        
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute([
            $number,
            $currentNumber['participant_name'],
            $currentNumber['participant_email'],
            $_SERVER['REMOTE_ADDR'] ?? 'admin',
            $_SERVER['HTTP_USER_AGENT'] ?? 'admin_panel'
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pago confirmado exitosamente',
            'data' => [
                'number' => $number,
                'participant_name' => $currentNumber['participant_name'],
                'participant_email' => $currentNumber['participant_email'],
                'confirmed_at' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error en confirm_payment.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
