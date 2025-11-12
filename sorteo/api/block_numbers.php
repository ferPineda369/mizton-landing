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
    // Limpiar reservas expiradas
    cleanExpiredReservations($pdo);
    
    // Obtener datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    $numbers = $input['numbers'] ?? [];
    $action = $input['action'] ?? 'block'; // 'block', 'unblock' o 'sync'
    $sessionId = $input['session_id'] ?? session_id();
    
    // Debug logging
    error_log("block_numbers.php - Action: $action, Numbers: " . json_encode($numbers) . ", Session: $sessionId");
    
    if (!is_array($numbers)) {
        echo json_encode([
            'success' => false,
            'message' => 'Números inválidos'
        ]);
        exit;
    }
    
    // Para 'sync', permitir arrays vacíos (deseleccionar todo)
    if (empty($numbers) && $action !== 'sync') {
        echo json_encode([
            'success' => false,
            'message' => 'Números inválidos'
        ]);
        exit;
    }
    
    // Validar números
    foreach ($numbers as $number) {
        if (!is_numeric($number) || $number < 1 || $number > 100) {
            echo json_encode([
                'success' => false,
                'message' => 'Número inválido: ' . $number
            ]);
            exit;
        }
    }
    
    if ($action === 'block') {
        // Bloquear números temporalmente
        $placeholders = str_repeat('?,', count($numbers) - 1) . '?';
        
        // Verificar que los números estén disponibles
        $checkSql = "SELECT number_value, status FROM sorteo_numbers 
                     WHERE number_value IN ($placeholders) 
                     AND status != 'available'";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute($numbers);
        $unavailable = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($unavailable)) {
            echo json_encode([
                'success' => false,
                'message' => 'Algunos números ya no están disponibles: ' . implode(', ', $unavailable)
            ]);
            exit;
        }
        
        // Crear tabla de bloqueos temporales si no existe
        $createTableSql = "
        CREATE TABLE IF NOT EXISTS sorteo_temp_blocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            number_value INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_number_session (number_value, session_id),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($createTableSql);
        
        // Limpiar bloqueos expirados
        $pdo->exec("DELETE FROM sorteo_temp_blocks WHERE expires_at < NOW()");
        
        // Verificar si algún número ya está bloqueado por otra sesión
        $blockCheckSql = "SELECT number_value FROM sorteo_temp_blocks 
                          WHERE number_value IN ($placeholders) 
                          AND session_id != ? 
                          AND expires_at > NOW()";
        $blockCheckStmt = $pdo->prepare($blockCheckSql);
        $blockCheckStmt->execute(array_merge($numbers, [$sessionId]));
        $blockedByOthers = $blockCheckStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($blockedByOthers)) {
            echo json_encode([
                'success' => false,
                'message' => 'Algunos números están siendo reservados por otros usuarios: ' . implode(', ', $blockedByOthers)
            ]);
            exit;
        }
        
        // Bloquear números (2 minutos)
        $expiresAt = date('Y-m-d H:i:s', time() + 120);
        
        // Eliminar bloqueos anteriores de esta sesión
        $deleteSql = "DELETE FROM sorteo_temp_blocks WHERE session_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$sessionId]);
        
        // Insertar nuevos bloqueos
        $insertSql = "INSERT INTO sorteo_temp_blocks (number_value, session_id, expires_at) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        
        foreach ($numbers as $number) {
            $insertStmt->execute([$number, $sessionId, $expiresAt]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Números bloqueados temporalmente',
            'expires_at' => $expiresAt,
            'blocked_numbers' => $numbers
        ]);
        
    } else if ($action === 'unblock') {
        // Desbloquear números de esta sesión
        $deleteSql = "DELETE FROM sorteo_temp_blocks WHERE session_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$sessionId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Números desbloqueados'
        ]);
        
    } else if ($action === 'sync') {
        // Sincronizar: eliminar todos los bloqueos de esta sesión y agregar los nuevos
        
        // Primero eliminar todos los bloqueos de esta sesión
        $deleteSql = "DELETE FROM sorteo_temp_blocks WHERE session_id = ?";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([$sessionId]);
        
        // Si hay números para bloquear, agregarlos
        if (!empty($numbers)) {
            // Verificar que los números estén disponibles
            $placeholders = str_repeat('?,', count($numbers) - 1) . '?';
            $checkSql = "SELECT number_value, status FROM sorteo_numbers 
                         WHERE number_value IN ($placeholders) 
                         AND status != 'available'";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute($numbers);
            $unavailable = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($unavailable)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Algunos números ya no están disponibles: ' . implode(', ', $unavailable)
                ]);
                exit;
            }
            
            // Crear tabla de bloqueos temporales si no existe
            $createTableSql = "
            CREATE TABLE IF NOT EXISTS sorteo_temp_blocks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                number_value INT NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_number_session (number_value, session_id),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $pdo->exec($createTableSql);
            
            // Calcular tiempo de expiración (2 minutos)
            $expiresAt = date('Y-m-d H:i:s', time() + (2 * 60));
            
            // Insertar nuevos bloqueos
            $insertSql = "INSERT INTO sorteo_temp_blocks (number_value, session_id, expires_at) VALUES (?, ?, ?)";
            $insertStmt = $pdo->prepare($insertSql);
            
            foreach ($numbers as $number) {
                $insertStmt->execute([$number, $sessionId, $expiresAt]);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Bloqueos sincronizados correctamente',
            'blocked_numbers' => $numbers,
            'expires_at' => isset($expiresAt) ? $expiresAt : null
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en block_numbers.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
