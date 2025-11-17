<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Solo POST permitido']);
    exit;
}

// Función para sanitizar preservando acentos
function sanitizePreservingAccents($input) {
    $input = trim($input);
    $input = preg_replace('/[<>"\']/', '', $input);
    return $input;
}

// Obtener datos
$fullName = sanitizePreservingAccents($_POST['fullName'] ?? '');
$phoneNumber = sanitizePreservingAccents($_POST['phoneNumber'] ?? '');

// Debug completo
$debug = [
    'received_data' => [
        'fullName_raw' => $_POST['fullName'] ?? 'NO_RECIBIDO',
        'fullName_sanitized' => $fullName,
        'phoneNumber' => $phoneNumber
    ],
    'encoding_info' => [
        'fullName_strlen' => strlen($fullName),
        'fullName_mb_strlen' => mb_strlen($fullName, 'UTF-8'),
        'fullName_bytes' => array_values(unpack('C*', $fullName)),
        'fullName_hex' => bin2hex($fullName),
        'server_encoding' => mb_internal_encoding(),
        'locale' => setlocale(LC_ALL, 0)
    ],
    'validation_tests' => [
        'length_check' => strlen($fullName) >= 3,
        'regex_test' => preg_match('/^[a-zA-ZáéíóúüÁÉÍÓÚÜñÑ\s]+$/', $fullName),
        'contains_accents' => preg_match('/[áéíóúüÁÉÍÓÚÜñÑ]/', $fullName),
        'contains_invalid_chars' => preg_match('/[^a-zA-ZáéíóúüÁÉÍÓÚÜñÑ\s]/', $fullName)
    ]
];

// Validación real
$errors = [];

if (!$fullName || strlen($fullName) < 3) {
    $errors[] = 'El nombre debe tener al menos 3 caracteres';
}

if (!preg_match('/^[a-zA-ZáéíóúüÁÉÍÓÚÜñÑ\s]+$/', $fullName)) {
    $errors[] = 'El nombre solo puede contener letras y espacios';
}

$debug['validation_result'] = [
    'errors' => $errors,
    'is_valid' => empty($errors)
];

// Casos de prueba específicos
$testCases = [
    'José María',
    'Müller',
    'Güero',
    'Ángela',
    'Niño'
];

$debug['test_cases'] = [];
foreach ($testCases as $testName) {
    $debug['test_cases'][$testName] = [
        'regex_match' => preg_match('/^[a-zA-ZáéíóúüÁÉÍÓÚÜñÑ\s]+$/', $testName),
        'bytes' => array_values(unpack('C*', $testName)),
        'hex' => bin2hex($testName)
    ];
}

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
