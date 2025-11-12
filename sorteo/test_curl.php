<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test con cURL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Test API con cURL</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_POST['test_api'])): ?>
                            <h5>Resultado del Test:</h5>
                            <?php
                            // Datos para el test
                            $postData = [
                                'number' => 77,
                                'fullName' => 'Test Usuario cURL',
                                'phoneNumber' => '2222012345'
                            ];
                            
                            // URL de la API
                            $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/register_number.php';
                            
                            // Configurar cURL
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                            
                            // Ejecutar petición
                            $response = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            $error = curl_error($ch);
                            curl_close($ch);
                            
                            echo "<div class='alert alert-info'>";
                            echo "<strong>Datos enviados:</strong><br>";
                            echo "Número: " . $postData['number'] . "<br>";
                            echo "Nombre: " . $postData['fullName'] . "<br>";
                            echo "Celular: " . $postData['phoneNumber'] . "<br>";
                            echo "URL: " . $url . "<br>";
                            echo "</div>";
                            
                            if ($error) {
                                echo "<div class='alert alert-danger'>";
                                echo "<strong>Error cURL:</strong> " . $error;
                                echo "</div>";
                            } else {
                                echo "<div class='alert alert-success'>";
                                echo "<strong>HTTP Code:</strong> " . $httpCode . "<br>";
                                echo "<strong>Respuesta:</strong><br>";
                                echo "<pre>" . htmlspecialchars($response) . "</pre>";
                                echo "</div>";
                                
                                // Intentar decodificar JSON
                                $json = json_decode($response, true);
                                if ($json) {
                                    echo "<div class='alert alert-info'>";
                                    echo "<strong>JSON Decodificado:</strong><br>";
                                    echo "<pre>" . print_r($json, true) . "</pre>";
                                    echo "</div>";
                                }
                            }
                            
                            // Verificar en base de datos
                            try {
                                require_once 'config/database.php';
                                $sql = "SELECT * FROM sorteo_numbers WHERE number_value = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([77]);
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                echo "<div class='alert alert-warning'>";
                                echo "<strong>Verificación en Base de Datos (Número 77):</strong><br>";
                                if ($result) {
                                    echo "<table class='table table-sm'>";
                                    foreach ($result as $key => $value) {
                                        echo "<tr><td><strong>$key</strong></td><td>" . ($value ?? 'NULL') . "</td></tr>";
                                    }
                                    echo "</table>";
                                } else {
                                    echo "No se encontró el número 77 en la base de datos.";
                                }
                                echo "</div>";
                                
                            } catch (Exception $e) {
                                echo "<div class='alert alert-danger'>";
                                echo "<strong>Error verificando BD:</strong> " . $e->getMessage();
                                echo "</div>";
                            }
                            ?>
                        <?php else: ?>
                            <p>Este test usa cURL para hacer una petición HTTP real a la API, evitando problemas de inclusión de archivos.</p>
                            
                            <form method="POST">
                                <button type="submit" name="test_api" class="btn btn-primary">
                                    Ejecutar Test con cURL
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="debug_reservas.php" class="btn btn-secondary">Ver Debug General</a>
                            <a href="index.php" class="btn btn-info">Volver al Sorteo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
