<?php
echo "<h2>Diagnóstico Avanzado de Rewrite</h2>";

echo "<h3>Variables del Servidor:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
$server_vars = [
    'REQUEST_URI', 'QUERY_STRING', 'REQUEST_METHOD', 'SCRIPT_NAME', 
    'SCRIPT_FILENAME', 'DOCUMENT_ROOT', 'SERVER_NAME', 'HTTP_HOST',
    'REDIRECT_STATUS', 'REDIRECT_URL', 'REDIRECT_QUERY_STRING'
];

foreach ($server_vars as $var) {
    $value = $_SERVER[$var] ?? 'No definido';
    echo "<tr><td><strong>$var</strong></td><td>$value</td></tr>";
}
echo "</table>";

echo "<h3>Verificar Archivos:</h3>";
$files_to_check = [
    '/usr/local/lsws/Example/html/.htaccess',
    '/usr/local/lsws/Example/html/news/.htaccess',
    '/usr/local/lsws/Example/html/news/post.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file existe</p>";
        if (is_readable($file)) {
            echo "<p style='color: green;'>✅ $file es legible</p>";
        } else {
            echo "<p style='color: red;'>❌ $file NO es legible</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ $file NO existe</p>";
    }
}

echo "<h3>Contenido del .htaccess principal:</h3>";
if (file_exists('/usr/local/lsws/Example/html/.htaccess')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('/usr/local/lsws/Example/html/.htaccess')) . "</pre>";
} else {
    echo "<p style='color: red;'>No se puede leer .htaccess</p>";
}

echo "<h3>Test de Regex:</h3>";
$test_urls = [
    'news/tendencias-clave-de-la-tokenizacin-rwa-en-2025',
    'news/tendencias-clave-de-la-tokenizacin-rwa-en-2025/test12',
    'test-simple'
];

foreach ($test_urls as $url) {
    echo "<p><strong>URL:</strong> $url</p>";
    
    // Test regex para news sin referido
    if (preg_match('/^news\/([a-zA-Z0-9-]+)\/?$/', $url, $matches)) {
        echo "<p style='color: green;'>✅ Coincide con regex news sin referido: slug = {$matches[1]}</p>";
    }
    
    // Test regex para news con referido
    if (preg_match('/^news\/([a-zA-Z0-9-]+)\/([a-zA-Z0-9]{6})\/?$/', $url, $matches)) {
        echo "<p style='color: green;'>✅ Coincide con regex news con referido: slug = {$matches[1]}, ref = {$matches[2]}</p>";
    }
    
    // Test regex simple
    if (preg_match('/^test-simple\/?$/', $url, $matches)) {
        echo "<p style='color: green;'>✅ Coincide con regex test-simple</p>";
    }
    
    echo "<hr>";
}
?>
