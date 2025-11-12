<?php
require_once 'config/database.php';

echo "<h2>Verificación del Número 77</h2>";

try {
    // Verificar número 77
    $sql = "SELECT * FROM sorteo_numbers WHERE number_value = 77";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<div style='color: green; font-weight: bold;'>✅ ¡ÉXITO! El número 77 SÍ se registró correctamente:</div>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        foreach ($result as $key => $value) {
            $color = ($key === 'participant_movil' && $value) ? 'background-color: lightgreen;' : '';
            echo "<tr style='$color'><td><strong>$key</strong></td><td>" . ($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
        
        echo "<p><strong>Conclusión:</strong> La API funciona correctamente cuando se llama con cURL.</p>";
        
    } else {
        echo "<div style='color: red;'>❌ El número 77 NO se encontró en la base de datos</div>";
    }
    
    // Verificar transacciones del número 77
    echo "<h3>Transacciones del Número 77:</h3>";
    $sql = "SELECT * FROM sorteo_transactions WHERE number_value = 77 ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($transactions) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Fecha</th><th>Participante</th><th>Celular</th><th>Acción</th><th>IP</th></tr>";
        foreach ($transactions as $trans) {
            echo "<tr>";
            echo "<td>" . $trans['created_at'] . "</td>";
            echo "<td>" . ($trans['participant_name'] ?? 'NULL') . "</td>";
            echo "<td style='background-color: lightblue;'>" . ($trans['participant_movil'] ?? 'NULL') . "</td>";
            echo "<td>" . $trans['action'] . "</td>";
            echo "<td>" . ($trans['ip_address'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay transacciones para el número 77</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="admin/">Ver en Panel Admin</a></p>
<p><a href="debug_reservas.php">Ver Debug General</a></p>
