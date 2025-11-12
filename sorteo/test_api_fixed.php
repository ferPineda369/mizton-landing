<?php
// Test directo de API sin problemas de headers
if (isset($_GET['test'])) {
    // Simular datos POST
    $_POST['number'] = 88;
    $_POST['fullName'] = 'Test Usuario';
    $_POST['phoneNumber'] = '2222012345';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capturar output de la API
    ob_start();
    include __DIR__ . '/api/register_number.php';
    $apiOutput = ob_get_clean();
    
    // Mostrar resultado
    header('Content-Type: application/json');
    echo $apiOutput;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Corregido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Test API de Registro - Versión Corregida</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h5>Datos de Prueba:</h5>
                            <ul>
                                <li><strong>Número:</strong> 88</li>
                                <li><strong>Nombre:</strong> Test Usuario</li>
                                <li><strong>Celular:</strong> 2222012345</li>
                            </ul>
                        </div>
                        
                        <button id="testBtn" class="btn btn-primary">Ejecutar Test de API</button>
                        
                        <div id="result" class="mt-4" style="display: none;">
                            <h5>Resultado de la API:</h5>
                            <pre id="apiResult" class="bg-dark text-light p-3"></pre>
                        </div>
                        
                        <div id="dbCheck" class="mt-4" style="display: none;">
                            <h5>Verificación en Base de Datos:</h5>
                            <div id="dbResult"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('testBtn').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Ejecutando...';
            
            try {
                // Llamar a la API
                const response = await fetch('?test=1', {
                    method: 'GET'
                });
                
                const text = await response.text();
                
                // Mostrar resultado
                document.getElementById('apiResult').textContent = text;
                document.getElementById('result').style.display = 'block';
                
                // Verificar en base de datos
                setTimeout(() => {
                    checkDatabase();
                }, 1000);
                
            } catch (error) {
                document.getElementById('apiResult').textContent = 'Error: ' + error.message;
                document.getElementById('result').style.display = 'block';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Ejecutar Test de API';
            }
        });
        
        async function checkDatabase() {
            try {
                const response = await fetch('debug_reservas.php');
                const html = await response.text();
                
                // Extraer solo la parte relevante
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const tables = doc.querySelectorAll('table');
                
                let dbHtml = '<p>Verificando número 88 en la base de datos...</p>';
                if (tables.length > 0) {
                    dbHtml += tables[0].outerHTML;
                }
                
                document.getElementById('dbResult').innerHTML = dbHtml;
                document.getElementById('dbCheck').style.display = 'block';
                
            } catch (error) {
                document.getElementById('dbResult').innerHTML = '<p class="text-danger">Error verificando base de datos: ' + error.message + '</p>';
                document.getElementById('dbCheck').style.display = 'block';
            }
        }
    </script>
</body>
</html>
