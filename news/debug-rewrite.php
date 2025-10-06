<?php
echo "<h2>Debug de Rewrite Rules</h2>";

echo "<h3>Variables $_GET:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>Variables $_SERVER relevantes:</h3>";
$server_vars = [
    'REQUEST_URI', 'QUERY_STRING', 'REQUEST_METHOD', 'SCRIPT_NAME', 
    'SCRIPT_FILENAME', 'DOCUMENT_ROOT', 'HTTP_HOST'
];

foreach ($server_vars as $var) {
    $value = $_SERVER[$var] ?? 'No definido';
    echo "<p><strong>$var:</strong> $value</p>";
}

echo "<h3>Test de Regex Manual:</h3>";
$test_patterns = [
    '^([a-zA-Z0-9]{6})/?$' => 'test12',
    '^([a-zA-Z0-9-]+)/([a-zA-Z0-9]{6})/?$' => 'mi-post/test12',
    '^([a-zA-Z0-9-]+)/?$' => 'mi-post-largo'
];

foreach ($test_patterns as $pattern => $test) {
    echo "<p><strong>Pattern:</strong> $pattern</p>";
    echo "<p><strong>Test:</strong> $test</p>";
    if (preg_match("/$pattern/", $test, $matches)) {
        echo "<p style='color: green;'>✅ Match: ";
        print_r($matches);
        echo "</p>";
    } else {
        echo "<p style='color: red;'>❌ No match</p>";
    }
    echo "<hr>";
}

echo "<h3>Verificar si llegó como referido:</h3>";
if (isset($_GET['ref'])) {
    echo "<p style='color: green;'>✅ Referido detectado: " . htmlspecialchars($_GET['ref']) . "</p>";
} else {
    echo "<p style='color: red;'>❌ No se detectó referido</p>";
}

echo "<h3>Enlaces de prueba:</h3>";
echo "<ul>";
echo "<li><a href='debug-rewrite.php?ref=test12'>Debug directo con ref=test12</a></li>";
echo "<li><a href='test12'>test12 (debería ir a index con referido)</a></li>";
echo "<li><a href='mi-post'>mi-post (debería ir a post)</a></li>";
echo "</ul>";
?>
