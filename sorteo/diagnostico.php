<?php
header('Content-Type: application/json');

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'servidor' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconocido',
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Desconocido'
    ],
    'archivos' => [
        'config_database' => file_exists('config/database.php'),
        'get_numbers_api' => file_exists('api/get_numbers.php'),
        'get_numbers_simple_api' => file_exists('api/get_numbers_simple.php'),
        'register_number_api' => file_exists('api/register_number.php'),
        'block_numbers_api' => file_exists('api/block_numbers.php')
    ],
    'permisos' => [
        'config_readable' => is_readable('config/database.php'),
        'api_readable' => is_readable('api/'),
        'api_writable' => is_writable('api/')
    ]
];

// Probar conexión a base de datos
try {
    require_once 'config/database.php';
    $diagnostico['base_datos'] = [
        'conexion' => 'exitosa',
        'tablas' => []
    ];
    
    // Verificar tablas
    $tables = ['sorteo_numbers', 'sorteo_transactions', 'sorteo_temp_blocks'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            $diagnostico['base_datos']['tablas'][$table] = [
                'existe' => true,
                'registros' => $count
            ];
        } catch (Exception $e) {
            $diagnostico['base_datos']['tablas'][$table] = [
                'existe' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
} catch (Exception $e) {
    $diagnostico['base_datos'] = [
        'conexion' => 'fallida',
        'error' => $e->getMessage()
    ];
}

// Probar APIs
$apis = [
    'get_numbers' => 'api/get_numbers.php',
    'get_numbers_simple' => 'api/get_numbers_simple.php'
];

foreach ($apis as $name => $path) {
    if (file_exists($path)) {
        $diagnostico['apis'][$name] = [
            'archivo_existe' => true,
            'tamaño' => filesize($path),
            'modificado' => date('Y-m-d H:i:s', filemtime($path))
        ];
    } else {
        $diagnostico['apis'][$name] = [
            'archivo_existe' => false
        ];
    }
}

echo json_encode($diagnostico, JSON_PRETTY_PRINT);
?>
