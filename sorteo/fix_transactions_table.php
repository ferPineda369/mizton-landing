<?php
require_once 'config/database.php';

echo "<h2>Corrección de Tabla sorteo_transactions</h2>";

try {
    // Hacer participant_email nullable
    echo "<p>Haciendo participant_email nullable...</p>";
    $pdo->exec("ALTER TABLE sorteo_transactions MODIFY participant_email VARCHAR(255) NULL");
    echo "<p style='color: green;'>✓ participant_email ahora es nullable</p>";
    
    // Hacer participant_name nullable también por consistencia
    echo "<p>Haciendo participant_name nullable...</p>";
    $pdo->exec("ALTER TABLE sorteo_transactions MODIFY participant_name VARCHAR(255) NULL");
    echo "<p style='color: green;'>✓ participant_name ahora es nullable</p>";
    
    // Verificar estructura actualizada
    echo "<h3>Estructura Actualizada de sorteo_transactions:</h3>";
    $sql = "DESCRIBE sorteo_transactions";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        $nullColor = $column['Null'] === 'YES' ? 'green' : 'red';
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td style='color: $nullColor; font-weight: bold;'>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green; font-weight: bold; margin-top: 20px;'>¡Corrección completada!</p>";
    echo "<p><strong>Ahora intenta hacer una nueva reserva.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="debug_reservas.php">Ver Debug de Reservas</a></p>
<p><a href="index.php">Volver al Sorteo</a></p>
