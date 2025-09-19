<?php
// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mostrar información de debug
echo "<h2>Test de Sesión - Landing</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Cookie Domain:</strong> " . ini_get('session.cookie_domain') . "</p>";
echo "<p><strong>Current Domain:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// Establecer variable de test
if (isset($_GET['set'])) {
    $_SESSION['test_referido'] = $_GET['set'];
    echo "<p><strong>Variable establecida:</strong> " . $_SESSION['test_referido'] . "</p>";
}

// Mostrar todas las variables de sesión
echo "<h3>Variables de Sesión:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<p><a href='?set=prueba123'>Establecer variable de test</a></p>";
echo "<p><a href='https://panel.mizton.cat/test_session_panel.php'>Ir al panel para verificar</a></p>";
?>
