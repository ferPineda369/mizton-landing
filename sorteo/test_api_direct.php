<?php
echo "<h2>Test Directo de API de Registro</h2>";

// Simular datos POST
$_POST['number'] = 99;
$_POST['fullName'] = 'Test Usuario';
$_POST['phoneNumber'] = '2222012345';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h3>Datos de prueba:</h3>";
echo "<p>Número: " . $_POST['number'] . "</p>";
echo "<p>Nombre: " . $_POST['fullName'] . "</p>";
echo "<p>Celular: " . $_POST['phoneNumber'] . "</p>";

echo "<h3>Ejecutando API register_number.php:</h3>";
echo "<pre>";

// Capturar output
ob_start();
include 'api/register_number.php';
$output = ob_get_clean();

echo htmlspecialchars($output);
echo "</pre>";

// Verificar si se registró
echo "<h3>Verificación en Base de Datos:</h3>";
require_once 'config/database.php';

try {
    $sql = "SELECT * FROM sorteo_numbers WHERE number_value = 99";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Número encontrado en base de datos:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($result as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>" . ($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Número NO encontrado en base de datos</p>";
    }
    
    // Verificar transacciones
    echo "<h3>Transacciones para número 99:</h3>";
    $sql = "SELECT * FROM sorteo_transactions WHERE number_value = 99 ORDER BY created_at DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($transactions) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Fecha</th><th>Participante</th><th>Celular</th><th>Acción</th></tr>";
        foreach ($transactions as $trans) {
            echo "<tr>";
            echo "<td>" . $trans['created_at'] . "</td>";
            echo "<td>" . ($trans['participant_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($trans['participant_movil'] ?? 'NULL') . "</td>";
            echo "<td>" . $trans['action'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay transacciones para el número 99</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="debug_reservas.php">Ver Debug General</a></p>
<p><a href="index.php">Volver al Sorteo</a></p>
