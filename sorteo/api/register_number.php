<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Log de inicio para debug
error_log("=== INICIO register_number.php ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Método no permitido - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Limpiar reservas expiradas
    cleanExpiredReservations($pdo);
    
    // Validar datos de entrada
    $number = filter_input(INPUT_POST, 'number', FILTER_VALIDATE_INT);
    $fullName = trim(filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $phoneNumber = trim(filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    error_log("Datos procesados - Número: $number, Nombre: $fullName, Celular: $phoneNumber");
    
    // Validaciones
    $errors = [];
    
    if (!$number || $number < 1 || $number > 100) {
        $errors[] = 'Número inválido';
    }
    
    if (!$fullName || strlen($fullName) < 3) {
        $errors[] = 'El nombre debe tener al menos 3 caracteres';
    }
    
    if (!$phoneNumber || !preg_match('/^[0-9]{10}$/', $phoneNumber)) {
        $errors[] = 'El número celular es obligatorio y debe tener exactamente 10 dígitos';
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $fullName)) {
        $errors[] = 'El nombre solo puede contener letras y espacios';
    }
    
    if (!empty($errors)) {
        error_log("Errores de validación: " . implode(', ', $errors));
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos: ' . implode(', ', $errors)
        ]);
        exit;
    }
    
    error_log("Validaciones pasadas, procediendo con la reserva...");
    
    // Verificar si el número está disponible
    $checkSql = "SELECT status, participant_movil FROM sorteo_numbers WHERE number_value = ?";
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
    
    if ($currentNumber['status'] !== 'available') {
        echo json_encode([
            'success' => false,
            'message' => 'El número ya no está disponible'
        ]);
        exit;
    }
    
    // Ya no verificamos duplicados por email, permitimos múltiples números por persona
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Calcular tiempo de expiración (15 minutos)
        $expirationTime = date('Y-m-d H:i:s', time() + (15 * 60));
        
        // Reservar el número
        $reserveSql = "UPDATE sorteo_numbers 
                       SET status = 'reserved',
                           participant_name = ?,
                           participant_movil = ?,
                           reserved_at = NOW(),
                           reservation_expires_at = ?
                       WHERE number_value = ? AND status = 'available'";
        
        $reserveStmt = $pdo->prepare($reserveSql);
        $result = $reserveStmt->execute([$fullName, $phoneNumber, $expirationTime, $number]);
        
        if (!$result || $reserveStmt->rowCount() === 0) {
            throw new Exception('No se pudo reservar el número. Puede que ya esté ocupado.');
        }
        
        // Registrar en el log de transacciones
        $logSql = "INSERT INTO sorteo_transactions 
                   (number_value, participant_name, participant_movil, participant_email, action, ip_address, user_agent) 
                   VALUES (?, ?, ?, NULL, 'reserved', ?, ?)";
        
        $logStmt = $pdo->prepare($logSql);
        $logResult = $logStmt->execute([
            $number,
            $fullName,
            $phoneNumber,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        if (!$logResult) {
            error_log("ERROR EN LOG DE TRANSACCIONES: " . print_r($logStmt->errorInfo(), true));
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // Log para debug
        error_log("REGISTRO EXITOSO - Número: $number, Participante: $fullName, Celular: $phoneNumber, Expira: $expirationTime");
        
        echo json_encode([
            'success' => true,
            'message' => 'Número reservado exitosamente',
            'data' => [
                'number' => $number,
                'participant_name' => $fullName,
                'participant_movil' => $phoneNumber,
                'expires_at' => $expirationTime,
                'expires_in_minutes' => 15
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error en register_number.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
