<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'mizton_db'; // Cambiar por el nombre real
    $username = 'root'; // Cambiar por el usuario real  
    $password = ''; // Cambiar por la contraseña real
    
    // Crear conexión PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Crear tabla de números si no existe
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS sorteo_numbers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        number_value INT NOT NULL UNIQUE,
        status ENUM('available', 'reserved', 'confirmed') DEFAULT 'available',
        participant_name VARCHAR(255) NULL,
        participant_email VARCHAR(255) NULL,
        reserved_at TIMESTAMP NULL,
        confirmed_at TIMESTAMP NULL,
        reservation_expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_number_value (number_value),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSql);
    
    // Inicializar números si la tabla está vacía
    $count = $pdo->query("SELECT COUNT(*) FROM sorteo_numbers")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO sorteo_numbers (number_value) VALUES (?)");
        for ($i = 1; $i <= 100; $i++) {
            $stmt->execute([$i]);
        }
    }
    
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
    
    // Obtener todos los números
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
    
    // Procesar datos
    $processedNumbers = [];
    foreach ($numbers as $number) {
        $processedNumbers[] = [
            'number_value' => (int)$number['number_value'],
            'status' => $number['status'],
            'participant_name' => $number['participant_name'],
            'participant_email' => $number['participant_email'],
            'reserved_at' => $number['reserved_at'],
            'confirmed_at' => $number['confirmed_at'],
            'reservation_expires_at' => $number['reservation_expires_at'],
            'is_blocked_by_other' => false,
            'block_expires_at' => null
        ];
    }
    
    // Estadísticas
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
    error_log("Error en get_numbers_simple.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
?>
