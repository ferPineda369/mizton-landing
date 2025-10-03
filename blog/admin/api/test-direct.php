<?php
/**
 * Prueba directa de API get-post sin conflictos de headers
 */

// Simular una llamada GET real
$_GET['id'] = 4;

// Capturar la salida
ob_start();

// Incluir la API
include 'get-post.php';

// Obtener la respuesta
$response = ob_get_clean();

// Mostrar resultado
echo "<h2>🧪 Prueba Directa de get-post.php</h2>";
echo "<h3>📤 Respuesta de la API:</h3>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars($response);
echo "</pre>";

// Verificar si es JSON válido
$decoded = json_decode($response, true);
if ($decoded) {
    echo "<h3>✅ JSON Válido</h3>";
    if ($decoded['success']) {
        echo "<p><strong>✅ API funcionando correctamente</strong></p>";
        echo "<p><strong>Post cargado:</strong> " . htmlspecialchars($decoded['post']['title']) . "</p>";
        echo "<p><strong>Categoría:</strong> " . $decoded['post']['category'] . "</p>";
        echo "<p><strong>Estado:</strong> " . $decoded['post']['status'] . "</p>";
        echo "<p><strong>Tags:</strong> " . (is_array($decoded['post']['tags']) ? implode(', ', $decoded['post']['tags']) : 'No tags') . "</p>";
    } else {
        echo "<p><strong>❌ Error en API:</strong> " . $decoded['error'] . "</p>";
    }
} else {
    echo "<h3>❌ Respuesta no es JSON válido</h3>";
}

echo "<p><a href='../index.php'>← Volver al Admin</a></p>";
?>
