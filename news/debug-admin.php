<?php
echo "<h1>Debug Admin Access</h1>";
echo "<p>Este archivo está en: /news/debug-admin.php</p>";
echo "<p>Si ves esto, el rewrite NO está interfiriendo con archivos PHP en /news/</p>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Hora: " . date('Y-m-d H:i:s') . "</p>";

// Verificar si el directorio admin existe
if (is_dir(__DIR__ . '/admin')) {
    echo "<p>✅ Directorio /admin existe</p>";
    if (file_exists(__DIR__ . '/admin/index.php')) {
        echo "<p>✅ Archivo /admin/index.php existe</p>";
    } else {
        echo "<p>❌ Archivo /admin/index.php NO existe</p>";
    }
} else {
    echo "<p>❌ Directorio /admin NO existe</p>";
}
?>
