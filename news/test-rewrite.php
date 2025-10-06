<?php
echo "<h2>Test de Rewrite Rules</h2>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No definido') . "</p>";
echo "<p><strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'No definido') . "</p>";
echo "<p><strong>GET slug:</strong> " . ($_GET['slug'] ?? 'No definido') . "</p>";
echo "<p><strong>GET ref:</strong> " . ($_GET['ref'] ?? 'No definido') . "</p>";
echo "<p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'No definido') . "</p>";

echo "<h3>URLs de prueba:</h3>";
echo "<ul>";
echo "<li><a href='/news/test-rewrite.php?slug=test-slug'>Directo con parámetros</a></li>";
echo "<li><a href='/news/tendencias-clave-de-la-tokenizacin-rwa-en-2025'>URL limpia sin referido</a></li>";
echo "<li><a href='/news/tendencias-clave-de-la-tokenizacin-rwa-en-2025/test12'>URL limpia con referido</a></li>";
echo "</ul>";
?>
