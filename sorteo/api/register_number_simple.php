<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'miztondb'; // Usar la misma configuración
    $username = 'michiuser'; // Usar el mismo usuario
    $password = 'yo96jiaEJKG7pwRmw2gY8K'; // Usar la misma contraseña
    
    // Crear conexión PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Limpiar reservas expiradas
    $cleanSql = "UPDATE sorteo_numbers 
                 SET status = 'available', 
                     participant_name = NULL, 
                     participant_email = NULL, 
                     reserved_at = NULL, 
                     reservation_expires_at = NULL 
                 WHERE status = 'reserved' 
                 AND reservation_expires_at < NOW()";
    $pdo->exec($cleanSql);
    
    // Validar datos de entrada
    $number = filter_input(INPUT_POST, 'number', FILTER_VALIDATE_INT);
    $fullName = trim(filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $phoneNumber = trim(filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    // Validaciones
    $errors = [];
    
    if (!$number || $number < 1 || $number > 100) {
        $errors[] = 'Número inválido';
    }
    
    if (!$fullName || strlen($fullName) < 3) {
        $errors[] = 'El nombre debe tener al menos 3 caracteres';
    }
    
    if (!$phoneNumber || !preg_match('/^[0-9]{10}$/', $phoneNumber)) {
        $errors[] = 'El número celular debe tener exactamente 10 dígitos';
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $fullName)) {
        $errors[] = 'El nombre solo puede contener letras y espacios';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos: ' . implode(', ', $errors)
        ]);
        exit;
    }
    
    // Verificar si el número está disponible
    $checkSql = "SELECT status FROM sorteo_numbers WHERE number_value = ?";
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
        
        // Crear tabla de transacciones si no existe
        $createLogTableSql = "
        CREATE TABLE IF NOT EXISTS sorteo_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            number_value INT NOT NULL,
            participant_name VARCHAR(255) NOT NULL,
            participant_movil VARCHAR(15) NULL,
            action ENUM('reserved', 'confirmed', 'expired', 'cancelled') NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($createLogTableSql);
        
        // Registrar en el log de transacciones
        $logSql = "INSERT INTO sorteo_transactions 
                   (number_value, participant_name, participant_movil, action, ip_address, user_agent) 
                   VALUES (?, ?, ?, 'reserved', ?, ?)";
        
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute([
            $number,
            $fullName,
            $phoneNumber,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Confirmar transacción
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Número reservado exitosamente',
            'data' => [
                'number' => $number,
                'participant_name' => $fullName,
                'expires_at' => $expirationTime,
                'expires_in_minutes' => 15
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error en register_number_simple.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
