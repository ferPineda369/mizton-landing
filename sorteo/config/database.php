<?php
// Configuración de base de datos para el sorteo
try {
    // Usar la misma configuración de la landing principal
    require_once dirname(__DIR__, 2) . '/bootstrap-landing.php';
    
    // Crear las tablas si no existen
    createSorteoTables($pdo);
    
} catch (Exception $e) {
    error_log("Error en configuración de base de datos del sorteo: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

function createSorteoTables($pdo) {
    // Tabla para los números del sorteo
    $sql_numbers = "
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
        INDEX idx_status (status),
        INDEX idx_reservation_expires (reservation_expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Tabla para el log de transacciones
    $sql_log = "
    CREATE TABLE IF NOT EXISTS sorteo_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        number_value INT NOT NULL,
        participant_name VARCHAR(255) NOT NULL,
        participant_email VARCHAR(255) NOT NULL,
        action ENUM('reserved', 'confirmed', 'expired', 'cancelled') NOT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_number_value (number_value),
        INDEX idx_email (participant_email),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Ejecutar las consultas
    $pdo->exec($sql_numbers);
    $pdo->exec($sql_log);
    
    // Inicializar los 100 números si la tabla está vacía
    $count = $pdo->query("SELECT COUNT(*) FROM sorteo_numbers")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO sorteo_numbers (number_value) VALUES (?)");
        for ($i = 1; $i <= 100; $i++) {
            $stmt->execute([$i]);
        }
    }
}

// Función para limpiar reservas expiradas
function cleanExpiredReservations($pdo) {
    $sql = "UPDATE sorteo_numbers 
            SET status = 'available', 
                participant_name = NULL, 
                participant_email = NULL, 
                reserved_at = NULL, 
                reservation_expires_at = NULL 
            WHERE status = 'reserved' 
            AND reservation_expires_at < NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Log de las reservas expiradas
    if ($stmt->rowCount() > 0) {
        error_log("Sorteo: Se limpiaron " . $stmt->rowCount() . " reservas expiradas");
    }
}

// Limpiar reservas expiradas al cargar
cleanExpiredReservations($pdo);
?>
