<?php
/**
 * Página de Reserva de Tokens del Marketplace
 * Sistema independiente para reservar tokens de proyectos sin smart contract
 */

session_start();
require_once __DIR__ . '/config/marketplace-config.php';
require_once __DIR__ . '/includes/marketplace-functions.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: https://panel.mizton.cat/login.php');
    exit;
}

// Obtener ID del proyecto
$projectId = $_GET['project_id'] ?? 0;

if (!$projectId) {
    header('Location: /marketplace/');
    exit;
}

// Obtener información completa del proyecto
$project = getCompleteProject($projectId);

if (!$project) {
    header('Location: /marketplace/');
    exit;
}

// Obtener información del usuario incluyendo wallet
$db = getMarketplaceDB();
$stmt = $db->prepare("SELECT userUser, nameUser, emailUser, wallet_address FROM tbluser WHERE idUser = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Guardar wallet_address en sesión si existe
if (!empty($user['wallet_address'])) {
    $_SESSION['wallet_address'] = $user['wallet_address'];
}

// Generar CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Reservar Tokens - ' . $project['name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/marketplace/assets/css/marketplace.css">
    
    <style>
        :root {
            --primary-color: #40916c;
            --primary-dark: #1b4332;
            --success-color: #52b788;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .reserve-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .reserve-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .reserve-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .reserve-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .reserve-header .project-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .reserve-header .project-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid white;
        }
        
        .reserve-body {
            padding: 40px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box strong {
            color: var(--primary-color);
        }
        
        .calculation-box {
            background: linear-gradient(135deg, #e7f5f0, #d8f3e8);
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .calculation-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .calculation-row:last-child {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: 10px;
        }
        
        .btn-reserve {
            background: var(--primary-color);
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-reserve:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(64, 145, 108, 0.3);
        }
        
        .btn-reserve:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }
        
        .payment-method-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .payment-method-card:hover {
            border-color: var(--primary-color);
            background: #f8f9fa;
        }
        
        .payment-method-card.selected {
            border-color: var(--primary-color);
            background: #e7f5f0;
        }
        
        .payment-method-card input[type="radio"] {
            margin-right: 10px;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: var(--primary-color);
            background: #f8f9fa;
        }
        
        .upload-area.dragover {
            border-color: var(--primary-color);
            background: #e7f5f0;
        }
    </style>
</head>
<body>

<div class="reserve-container">
    <!-- Header -->
    <div class="reserve-card">
        <div class="reserve-header">
            <h1><i class="bi bi-coin me-2"></i>Reservar Tokens</h1>
            <div class="project-info">
                <?php if ($project['logo_url']): ?>
                    <img src="<?php echo htmlspecialchars($project['logo_url']); ?>" 
                         alt="<?php echo htmlspecialchars($project['name']); ?>" 
                         class="project-logo">
                <?php endif; ?>
                <div>
                    <h3 class="mb-0"><?php echo htmlspecialchars($project['name']); ?></h3>
                    <small><?php echo htmlspecialchars($project['token_symbol']); ?> Token</small>
                </div>
            </div>
        </div>
        
        <div class="reserve-body">
            <!-- Información Importante -->
            <div class="alert alert-info alert-custom">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Importante:</strong> Esta es una reserva de tokens. Una vez que el proyecto lance su smart contract, 
                recibirás tus tokens en la wallet que especifiques.
            </div>
            
            <!-- Formulario de Reserva -->
            <form id="reserveForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                
                <!-- Sección 1: Cantidad de Tokens -->
                <div class="form-section">
                    <h3><i class="bi bi-calculator me-2"></i>Cantidad de Tokens</h3>
                    
                    <div class="info-box">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Precio por Token:</strong> 
                                $<?php echo number_format($project['token_price_usd'], 2); ?> USD
                            </div>
                            <div class="col-md-6">
                                <strong>Disponibles:</strong> 
                                <?php 
                                $available = $project['total_supply'] - $project['circulating_supply'];
                                echo number_format($available); 
                                ?> tokens
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="token_amount" class="form-label">Cantidad de Tokens a Reservar</label>
                        <input type="number" 
                               class="form-control form-control-lg" 
                               id="token_amount" 
                               name="token_amount" 
                               min="1" 
                               step="1"
                               value="1"
                               required>
                        <small class="text-muted">Mínimo: 1 token (solo números enteros)</small>
                    </div>
                    
                    <!-- Cálculo Automático -->
                    <div class="calculation-box">
                        <div class="calculation-row">
                            <span>Tokens:</span>
                            <strong id="calc_tokens">0</strong>
                        </div>
                        <div class="calculation-row">
                            <span>Precio por Token:</span>
                            <strong>$<?php echo number_format($project['token_price_usd'], 2); ?> USD</strong>
                        </div>
                        <div class="calculation-row">
                            <span>Total a Pagar:</span>
                            <strong id="calc_total">$0.00 USD</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 2: Wallet Address -->
                <div class="form-section">
                    <h3><i class="bi bi-wallet2 me-2"></i>Wallet para Recibir Tokens</h3>
                    
                    <div class="alert alert-warning alert-custom">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> Asegúrate de proporcionar una wallet válida en la red 
                        <strong><?php echo htmlspecialchars($project['blockchain_network']); ?></strong>. 
                        Los tokens se enviarán a esta dirección.
                    </div>
                    
                    <div class="mb-3">
                        <label for="wallet_address" class="form-label">Dirección de Wallet</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="wallet_address" 
                               name="wallet_address" 
                               placeholder="0x..." 
                               value="<?php echo htmlspecialchars($user['wallet_address'] ?? ''); ?>"
                               required>
                        <small class="text-muted">Red: <?php echo htmlspecialchars($project['blockchain_network']); ?></small>
                    </div>
                </div>
                
                <!-- Sección 3: Método de Pago -->
                <div class="form-section">
                    <h3><i class="bi bi-credit-card me-2"></i>Método de Pago</h3>
                    
                    <div class="payment-method-card" data-method="crypto">
                        <input type="radio" name="payment_method" value="crypto" id="method_crypto" required>
                        <label for="method_crypto" class="mb-0">
                            <i class="bi bi-currency-bitcoin me-2"></i>
                            <strong>Criptomonedas</strong>
                            <small class="d-block text-muted">BNB, USDT, BUSD en BSC</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" data-method="bank_transfer">
                        <input type="radio" name="payment_method" value="bank_transfer" id="method_bank">
                        <label for="method_bank" class="mb-0">
                            <i class="bi bi-bank me-2"></i>
                            <strong>Transferencia Bancaria</strong>
                            <small class="d-block text-muted">Depósito o transferencia</small>
                        </label>
                    </div>
                    
                    <div class="payment-method-card" data-method="other">
                        <input type="radio" name="payment_method" value="other" id="method_other">
                        <label for="method_other" class="mb-0">
                            <i class="bi bi-three-dots me-2"></i>
                            <strong>Otro</strong>
                            <small class="d-block text-muted">Especificar en notas</small>
                        </label>
                    </div>
                </div>
                
                <!-- Sección 4: Notas -->
                <div class="form-section">
                    <h3><i class="bi bi-chat-left-text me-2"></i>Notas Adicionales (Opcional)</h3>
                    
                    <div class="mb-3">
                        <textarea class="form-control" 
                                  id="user_notes" 
                                  name="user_notes" 
                                  rows="4" 
                                  placeholder="Agrega cualquier información adicional sobre tu reserva..."></textarea>
                    </div>
                </div>
                
                <!-- Botón de Envío -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn-reserve" id="submitBtn">
                        <i class="bi bi-check-circle me-2"></i>Crear Reserva
                    </button>
                    <a href="/marketplace/project.php?code=<?php echo htmlspecialchars($project['project_code']); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Proyecto
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    const tokenPrice = <?php echo $project['token_price_usd']; ?>;
    
    // Calcular total automáticamente al cargar y al cambiar
    function updateCalculation() {
        const tokens = parseInt($('#token_amount').val()) || 0;
        const total = tokens * tokenPrice;
        
        $('#calc_tokens').text(tokens);
        $('#calc_total').text('$' + total.toFixed(2) + ' USD');
    }
    
    // Calcular al cargar la página
    updateCalculation();
    
    // Calcular al cambiar el valor
    $('#token_amount').on('input', updateCalculation);
    
    // Seleccionar método de pago
    $('.payment-method-card').on('click', function() {
        $('.payment-method-card').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
    
    // Enviar formulario
    $('#reserveForm').on('submit', function(e) {
        e.preventDefault();
        
        const tokens = parseFloat($('#token_amount').val());
        if (tokens < 1) {
            alert('La cantidad mínima es 1 token');
            return;
        }
        
        const wallet = $('#wallet_address').val().trim();
        if (!wallet.match(/^0x[a-fA-F0-9]{40}$/)) {
            alert('Por favor ingresa una dirección de wallet válida');
            return;
        }
        
        $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Procesando...');
        
        $.ajax({
            url: '/marketplace/ajax/reserve-token.ajax.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('¡Reserva creada exitosamente!\n\nAhora debes realizar el pago y subir el comprobante.');
                    window.location.href = 'https://panel.mizton.cat/marketplace-reserves.php?reserve_id=' + response.reserve_id;
                } else {
                    alert('Error: ' + (response.error || 'No se pudo crear la reserva'));
                    $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Crear Reserva');
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
                $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Crear Reserva');
            }
        });
    });
});
</script>

</body>
</html>
