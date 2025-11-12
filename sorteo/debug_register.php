<?php
// Debug para la API de registro
header('Content-Type: application/json');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'files' => $_FILES,
    'headers' => getallheaders(),
    'tests' => []
];

// Test 1: Verificar configuración
try {
    require_once 'config/database.php';
    $debug['tests']['config'] = ['success' => true, 'pdo_exists' => isset($pdo)];
} catch (Exception $e) {
    $debug['tests']['config'] = ['success' => false, 'error' => $e->getMessage()];
    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

// Test 2: Simular registro si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = filter_input(INPUT_POST, 'number', FILTER_VALIDATE_INT);
    $fullName = trim(filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    
    $debug['tests']['validation'] = [
        'number' => $number,
        'fullName' => $fullName,
        'number_valid' => ($number && $number >= 1 && $number <= 100),
        'name_valid' => ($fullName && strlen($fullName) >= 3)
    ];
    
    // Test 3: Verificar número en BD
    try {
        $stmt = $pdo->prepare("SELECT * FROM sorteo_numbers WHERE number_value = ?");
        $stmt->execute([$number]);
        $numberData = $stmt->fetch();
        
        $debug['tests']['number_check'] = [
            'exists' => $numberData ? true : false,
            'current_status' => $numberData['status'] ?? null,
            'participant' => $numberData['participant_name'] ?? null
        ];
    } catch (Exception $e) {
        $debug['tests']['number_check'] = ['error' => $e->getMessage()];
    }
}

// Test 4: Verificar tablas
try {
    $tables = ['sorteo_numbers', 'sorteo_transactions'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        $debug['tests']['tables'][$table] = $count;
    }
} catch (Exception $e) {
    $debug['tests']['tables'] = ['error' => $e->getMessage()];
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
