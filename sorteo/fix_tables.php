<?php
require_once 'config/database.php';

echo "<h2>Verificación y Corrección de Tablas</h2>";

try {
    // Verificar si existe la tabla sorteo_transactions
    $sql = "SHOW TABLES LIKE 'sorteo_transactions'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "<p style='color: red;'>La tabla sorteo_transactions NO existe. Creándola...</p>";
        
        // Crear tabla de transacciones
        $createSql = "CREATE TABLE sorteo_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            number_value INT NOT NULL,
            participant_name VARCHAR(255),
            participant_movil VARCHAR(15),
            action VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_number_value (number_value),
            INDEX idx_participant_movil (participant_movil),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createSql);
        echo "<p style='color: green;'>Tabla sorteo_transactions creada exitosamente!</p>";
    } else {
        echo "<p style='color: green;'>La tabla sorteo_transactions existe.</p>";
        
        // Verificar si tiene la columna participant_movil
        $sql = "SHOW COLUMNS FROM sorteo_transactions LIKE 'participant_movil'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $columnExists = $stmt->fetch();
        
        if (!$columnExists) {
            echo "<p style='color: orange;'>Agregando columna participant_movil...</p>";
            $pdo->exec("ALTER TABLE sorteo_transactions ADD COLUMN participant_movil VARCHAR(15) AFTER participant_name");
            echo "<p style='color: green;'>Columna participant_movil agregada!</p>";
        }
    }
    
    // Verificar tabla sorteo_numbers
    $sql = "SHOW COLUMNS FROM sorteo_numbers LIKE 'participant_movil'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "<p style='color: orange;'>Agregando columna participant_movil a sorteo_numbers...</p>";
        $pdo->exec("ALTER TABLE sorteo_numbers ADD COLUMN participant_movil VARCHAR(15) AFTER participant_email");
        echo "<p style='color: green;'>Columna participant_movil agregada a sorteo_numbers!</p>";
    } else {
        echo "<p style='color: green;'>La columna participant_movil existe en sorteo_numbers.</p>";
    }
    
    // Mostrar estructura actual
    echo "<h3>Estructura Actual de sorteo_numbers:</h3>";
    $sql = "DESCRIBE sorteo_numbers";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Estructura Actual de sorteo_transactions:</h3>";
    $sql = "DESCRIBE sorteo_transactions";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold;'>¡Verificación completada!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="debug_reservas.php">Ver Debug de Reservas</a></p>
<p><a href="admin/">Ir al Panel Admin</a></p>
