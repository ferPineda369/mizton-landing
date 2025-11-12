<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Simple de Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Test de Registro de Boleto</h4>
                    </div>
                    <div class="card-body">
                        <form id="testForm">
                            <div class="mb-3">
                                <label for="number" class="form-label">Número del Boleto</label>
                                <input type="number" class="form-control" id="number" name="number" min="1" max="100" value="88" required>
                            </div>
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="fullName" name="fullName" value="Test Usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">Número Celular</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="2222012345" pattern="[0-9]{10}" maxlength="10" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Boleto</button>
                        </form>
                        
                        <div id="result" class="mt-4" style="display: none;">
                            <h5>Resultado:</h5>
                            <pre id="resultContent"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            
            try {
                console.log('Enviando datos:', Object.fromEntries(formData));
                
                const response = await fetch('api/register_number.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                resultContent.textContent = text;
                resultDiv.style.display = 'block';
                
                // Intentar parsear como JSON
                try {
                    const json = JSON.parse(text);
                    console.log('Parsed JSON:', json);
                } catch (jsonError) {
                    console.error('No es JSON válido:', jsonError);
                }
                
            } catch (error) {
                console.error('Error:', error);
                resultContent.textContent = 'Error: ' + error.message;
                resultDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
