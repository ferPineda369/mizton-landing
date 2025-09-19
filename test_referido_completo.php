<?php
// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capturar referido si viene por GET
if (isset($_GET['ref'])) {
    $_SESSION['referido'] = preg_replace('/[^a-z0-9]/', '', strtolower($_GET['ref']));
}

// Log detallado
$logFile = __DIR__ . '/landing_debug.log';
$timestamp = date('Y-m-d H:i:s');
file_put_contents($logFile, "[$timestamp] TEST COMPLETO INICIADO\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] GET ref: " . (isset($_GET['ref']) ? $_GET['ref'] : 'NO EXISTE') . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] SESSION referido: " . (isset($_SESSION['referido']) ? $_SESSION['referido'] : 'NO EXISTE') . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Session ID: " . session_id() . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Cookie domain: " . ini_get('session.cookie_domain') . "\n", FILE_APPEND);
file_put_contents($logFile, "[$timestamp] Todas las variables SESSION: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

include 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Referido Completo - Mizton</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .info-box { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error-box { background: #ffe7e7; border: 1px solid #ffb3b3; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success-box { background: #e7ffe7; border: 1px solid #b3ffb3; padding: 15px; margin: 10px 0; border-radius: 5px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #005a87; }
        .debug-info { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin: 10px 0; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test Referido Completo - Mizton</h1>
        
        <div class="info-box">
            <h3>📋 Información de la Prueba</h3>
            <p><strong>URL actual:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
            <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <div class="debug-info">
            <h3>🔧 Debug de Variables</h3>
            <p><strong>GET 'ref':</strong> <?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '❌ NO EXISTE'; ?></p>
            <p><strong>SESSION 'referido':</strong> <?php echo isset($_SESSION['referido']) ? htmlspecialchars($_SESSION['referido']) : '❌ NO EXISTE'; ?></p>
            <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
            <p><strong>Cookie Domain:</strong> <?php echo ini_get('session.cookie_domain'); ?></p>
        </div>

        <?php if (isset($_SESSION['referido'])): ?>
        <div class="success-box">
            <h3>✅ Referido Detectado</h3>
            <p>Referido guardado en sesión: <strong><?php echo htmlspecialchars($_SESSION['referido']); ?></strong></p>
        </div>
        <?php else: ?>
        <div class="error-box">
            <h3>❌ No hay Referido</h3>
            <p>No se detectó ningún referido en la sesión.</p>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <h3>🧪 Configuración JavaScript</h3>
            <div class="debug-info">
                <pre><?php echo json_encode($landingConfig, JSON_PRETTY_PRINT); ?></pre>
            </div>
        </div>

        <div class="info-box">
            <h3>🎯 Pruebas de Navegación</h3>
            <p>Prueba los siguientes enlaces:</p>
            
            <button onclick="window.location.href='?ref=test123'">
                🔗 Cargar con ?ref=test123
            </button>
            
            <button onclick="testRegisterNavigation()">
                🚀 Ir a Registro (con JS)
            </button>
            
            <button onclick="window.open('https://panel.mizton.cat/register.php?ref=<?php echo isset($_SESSION['referido']) ? $_SESSION['referido'] : 'manual123'; ?>', '_blank')">
                🔗 Ir a Registro (manual con ref)
            </button>
            
            <button onclick="window.open('https://panel.mizton.cat/register.php', '_blank')">
                🔗 Ir a Registro (sin ref)
            </button>
        </div>

        <div class="info-box">
            <h3>📊 Logs en Tiempo Real</h3>
            <p>Los logs se están guardando en: <code>landing_debug.log</code></p>
            <button onclick="window.open('landing_debug.log', '_blank')">
                📄 Ver Landing Debug Log
            </button>
            <button onclick="window.open('https://panel.mizton.cat/debug_viewer.php', '_blank')">
                📄 Ver Panel Debug Log
            </button>
        </div>
    </div>

    <script>
        // Mostrar configuración en consola
        console.log('MIZTON_CONFIG:', window.MIZTON_CONFIG);
        
        function testRegisterNavigation() {
            console.log('Probando navegación con JavaScript...');
            
            if (window.MIZTON_CONFIG && window.MIZTON_CONFIG.register_url) {
                let registerUrl = window.MIZTON_CONFIG.register_url;
                
                // Si hay referido en la configuración, añadirlo como parámetro URL
                if (window.MIZTON_CONFIG.referido) {
                    const separator = registerUrl.includes('?') ? '&' : '?';
                    registerUrl += separator + 'ref=' + encodeURIComponent(window.MIZTON_CONFIG.referido);
                    console.log('URL con referido:', registerUrl);
                } else {
                    console.log('No hay referido en configuración');
                }
                
                window.open(registerUrl, '_blank');
            } else {
                console.log('No hay configuración disponible');
                alert('Error: No hay configuración disponible');
            }
        }
        
        // Auto-log cada 3 segundos para monitoreo
        setInterval(() => {
            console.log('Estado actual - Referido:', window.MIZTON_CONFIG?.referido || 'NO EXISTE');
        }, 3000);
    </script>
</body>
</html>
