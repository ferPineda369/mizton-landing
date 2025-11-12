<?php
// Archivo de prueba para verificar la conexión a la base de datos
header('Content-Type: application/json');

try {
    // Configuración de base de datos (ajustar según sea necesario)
    $host = 'localhost';
    $dbname = 'miztondb'; // Usar la misma configuración
    $username = 'michiuser'; // Usar el mismo usuario
    $password = 'yo96jiaEJKG7pwRmw2gY8K'; // Usar la misma contraseña
    
    // Crear conexión PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Probar consulta simple
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexión exitosa a la base de datos',
        'test_result' => $result,
        'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión',
        'error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>
