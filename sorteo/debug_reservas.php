<?php
require_once 'config/database.php';

echo "<h2>Debug de Reservas</h2>";

try {
    // Verificar números reservados
    echo "<h3>Números Reservados:</h3>";
    $sql = "SELECT * FROM sorteo_numbers WHERE status IN ('reserved', 'confirmed') ORDER BY number_value ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($numbers)) {
        echo "<p style='color: red;'>No hay números reservados o confirmados</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Número</th><th>Estado</th><th>Participante</th><th>Celular</th><th>Reservado</th><th>Expira</th></tr>";
        foreach ($numbers as $number) {
            echo "<tr>";
            echo "<td>" . $number['number_value'] . "</td>";
            echo "<td>" . $number['status'] . "</td>";
            echo "<td>" . ($number['participant_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($number['participant_movil'] ?? 'NULL') . "</td>";
            echo "<td>" . ($number['reserved_at'] ?? 'NULL') . "</td>";
            echo "<td>" . ($number['reservation_expires_at'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar transacciones
    echo "<h3>Transacciones Recientes:</h3>";
    $sql = "SELECT * FROM sorteo_transactions ORDER BY created_at DESC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($transactions)) {
        echo "<p style='color: red;'>No hay transacciones registradas</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Fecha</th><th>Número</th><th>Participante</th><th>Celular</th><th>Acción</th><th>IP</th></tr>";
        foreach ($transactions as $transaction) {
            echo "<tr>";
            echo "<td>" . $transaction['created_at'] . "</td>";
            echo "<td>" . $transaction['number_value'] . "</td>";
            echo "<td>" . ($transaction['participant_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($transaction['participant_movil'] ?? 'NULL') . "</td>";
            echo "<td>" . $transaction['action'] . "</td>";
            echo "<td>" . ($transaction['ip_address'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar estructura de tablas
    echo "<h3>Estructura de Tabla sorteo_numbers:</h3>";
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
    
    echo "<h3>Estructura de Tabla sorteo_transactions:</h3>";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
