<?php
header('Content-Type: application/json');

// Test de la API sync
$testSessionId = 'test_session_' . time();

echo json_encode([
    'test_info' => 'Probando API sync de bloqueos',
    'session_id' => $testSessionId,
    'tests' => []
], JSON_PRETTY_PRINT);

// Test 1: Sync con números [20, 33]
echo "\n\n=== TEST 1: Sync números [20, 33] ===\n";
$payload1 = [
    'numbers' => [20, 33],
    'action' => 'sync',
    'session_id' => $testSessionId
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/landing/sorteo/api/block_numbers.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload1));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response1 = curl_exec($ch);
echo "Payload: " . json_encode($payload1, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . $response1 . "\n";

// Test 2: Sync con números [20, 33, 45] (agregar 45, mantener 20 y 33)
echo "\n\n=== TEST 2: Sync números [20, 33, 45] ===\n";
$payload2 = [
    'numbers' => [20, 33, 45],
    'action' => 'sync',
    'session_id' => $testSessionId
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload2));
$response2 = curl_exec($ch);
echo "Payload: " . json_encode($payload2, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . $response2 . "\n";

// Test 3: Sync con números [45] (liberar 20 y 33, mantener solo 45)
echo "\n\n=== TEST 3: Sync números [45] ===\n";
$payload3 = [
    'numbers' => [45],
    'action' => 'sync',
    'session_id' => $testSessionId
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload3));
$response3 = curl_exec($ch);
echo "Payload: " . json_encode($payload3, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . $response3 . "\n";

// Test 4: Sync con números [] (liberar todos)
echo "\n\n=== TEST 4: Sync números [] ===\n";
$payload4 = [
    'numbers' => [],
    'action' => 'sync',
    'session_id' => $testSessionId
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload4));
$response4 = curl_exec($ch);
echo "Payload: " . json_encode($payload4, JSON_PRETTY_PRINT) . "\n";
echo "Response: " . $response4 . "\n";

curl_close($ch);

// Verificar estado final de la tabla
echo "\n\n=== ESTADO FINAL DE BLOQUEOS ===\n";
try {
    require_once 'config/database.php';
    $stmt = $pdo->query("SELECT * FROM sorteo_temp_blocks WHERE session_id = '$testSessionId'");
    $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Bloqueos restantes: " . json_encode($blocks, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error verificando bloqueos: " . $e->getMessage() . "\n";
}
?>
