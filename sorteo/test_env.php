<?php
// Test de variables de entorno
require_once 'config/database.php';

echo "<h2>🔧 Verificación de Variables de Entorno</h2>";

echo "<div style='background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>📋 Variables de Base de Datos</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f5f5f5;'>";
echo "<th style='padding: 10px;'>Variable</th><th style='padding: 10px;'>Valor</th><th style='padding: 10px;'>Fuente</th>";
echo "</tr>";

$dbVars = [
    'DB_HOST' => $host ?? 'No definido',
    'DB_NAME' => $dbname ?? 'No definido', 
    'DB_USER' => $username ?? 'No definido',
    'DB_PASS' => isset($password) ? str_repeat('*', strlen($password)) : 'No definido'
];

foreach ($dbVars as $var => $value) {
    $source = isset($_ENV[$var]) ? '.env' : 'Valor por defecto';
    $bgColor = isset($_ENV[$var]) ? 'background: #d4edda;' : 'background: #fff3cd;';
    
    echo "<tr style='$bgColor'>";
    echo "<td style='padding: 10px;'><strong>$var</strong></td>";
    echo "<td style='padding: 10px;'>$value</td>";
    echo "<td style='padding: 10px;'>$source</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Test de conexión
echo "<div style='background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>🔌 Test de Conexión a Base de Datos</h3>";

try {
    if (isset($pdo)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 3px;'>";
        echo "<strong>✅ Conexión exitosa</strong><br>";
        echo "Host: $host<br>";
        echo "Base de datos: $dbname<br>";
        echo "Usuario: $username<br>";
        
        // Test de consulta simple
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM sorteo_numbers");
        $result = $stmt->fetch();
        echo "Total de números en sorteo: " . $result['total'];
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 3px;'>";
        echo "<strong>❌ No se pudo establecer conexión</strong>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 3px;'>";
    echo "<strong>❌ Error de conexión:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "</div>";

// Mostrar archivos .env disponibles
echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>📁 Archivos de Configuración</h3>";

$envFiles = [
    __DIR__ . '/../.env' => '.env (principal)',
    __DIR__ . '/../.env.example' => '.env.example (plantilla)'
];

foreach ($envFiles as $path => $name) {
    $exists = file_exists($path);
    $icon = $exists ? '✅' : '❌';
    $status = $exists ? 'Existe' : 'No existe';
    
    echo "<p><strong>$icon $name:</strong> $status</p>";
    
    if ($exists) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "<small style='color: #666; margin-left: 20px;'>Tamaño: $size bytes | Modificado: $modified</small><br>";
    }
}

echo "</div>";

echo "<div style='margin-top: 30px;'>";
echo "<a href='../admin/' style='color: #007bff;'>🔧 Panel Admin</a> | ";
echo "<a href='index.php' style='color: #007bff;'>🎄 Volver al Sorteo</a>";
echo "</div>";
?>
