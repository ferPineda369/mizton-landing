<?php
echo "<h2>Test directo de post.php</h2>";

echo "<h3>Enlaces de prueba directos:</h3>";
echo "<ul>";
echo "<li><a href='post.php?slug=tendencias-clave-de-la-tokenizacin-rwa-en-2025'>Post directo sin referido</a></li>";
echo "<li><a href='post.php?slug=tendencias-clave-de-la-tokenizacin-rwa-en-2025&ref=test12'>Post directo con referido</a></li>";
echo "</ul>";

echo "<h3>Verificar que post.php existe:</h3>";
if (file_exists('post.php')) {
    echo "<p style='color: green;'>✅ post.php existe</p>";
} else {
    echo "<p style='color: red;'>❌ post.php NO existe</p>";
}

echo "<h3>Verificar archivos de configuración:</h3>";
if (file_exists('config/blog-config.php')) {
    echo "<p style='color: green;'>✅ config/blog-config.php existe</p>";
} else {
    echo "<p style='color: red;'>❌ config/blog-config.php NO existe</p>";
}

if (file_exists('includes/blog-functions.php')) {
    echo "<p style='color: green;'>✅ includes/blog-functions.php existe</p>";
} else {
    echo "<p style='color: red;'>❌ includes/blog-functions.php NO existe</p>";
}
?>
