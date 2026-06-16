<?php
// Versión independiente del simulador para presentación

// Obtener tasa de cambio USD-MXN (versión simplificada)
function getExchangeRates() {
    $result = [
        'bnb_usd' => 600,     // Precio BNB estimado
        'usd_mxn' => 17.28,   // Tasa MXN estimada
        'success' => true,
        'source' => 'presentation'
    ];
    
    // Intentar obtener tasa real desde API
    $coingecko_url = 'https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=usd,mxn';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    $response = @file_get_contents($coingecko_url, false, $context);
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['binancecoin']['usd']) && isset($data['binancecoin']['mxn'])) {
            $bnb_usd = floatval($data['binancecoin']['usd']);
            $bnb_mxn = floatval($data['binancecoin']['mxn']);
            $usd_mxn = $bnb_mxn / $bnb_usd;
            
            $result['bnb_usd'] = $bnb_usd;
            $result['usd_mxn'] = $usd_mxn;
            $result['source'] = 'coingecko';
        }
    }
    
    return $result;
}

$exchangeRates = getExchangeRates();
$tasaCambioMXN = $exchangeRates['usd_mxn'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador Miztel Commissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* ESTILOS DARK-NEON MIZTEL FORZADOS */
        :root {
            --cyber: #00d9ff;
            --magenta: #ff0080;
            --terracota: #e85d04;
            --purple: #7b2ff7;
            --green: #00c896;
            --bg-1: #1a1a2e;
            --bg-2: #16213e;
            --bg-3: #0f3460;
            --text: #ffffff;
            --text-muted: rgba(255,255,255,0.6);
            --border: rgba(0,217,255,0.15);
            --card-bg: rgba(255,255,255,0.04);
            --card-hover: rgba(0,217,255,0.08);
            --radius: 16px;
            --radius-lg: 24px;
            --transition: 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 50%, var(--bg-3) 100%) !important;
            background-attachment: fixed !important;
            color: var(--text) !important;
            font-family: 'Space Grotesk', system-ui, sans-serif !important;
            font-weight: 400 !important;
            line-height: 1.6 !important;
            overflow-x: hidden !important;
            min-height: 100vh !important;
        }
        
        .container-fluid {
            padding: 20px !important;
        }
        
        .card {
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: 0 0 30px rgba(0, 217, 255, 0.1) !important;
            backdrop-filter: blur(20px) !important;
            background: var(--card-bg) !important;
        }
        
        .card-header {
            border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important;
            font-weight: 700 !important;
            border-bottom: 1px solid var(--border) !important;
        }
        
        .card-header.bg-primary {
            background: linear-gradient(45deg, var(--cyber), var(--magenta)) !important;
            color: var(--text) !important;
        }
        
        .card-header.bg-warning {
            background: linear-gradient(45deg, var(--terracota), var(--magenta)) !important;
            color: var(--text) !important;
        }
        
        .card-header.bg-success {
            background: linear-gradient(45deg, var(--green), var(--cyber)) !important;
            color: var(--text) !important;
        }
        
        .btn {
            border-radius: var(--radius) !important;
            font-weight: 600 !important;
            border: 2px solid var(--border) !important;
            transition: var(--transition) !important;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--cyber), var(--magenta)) !important;
            border-color: var(--cyber) !important;
            color: var(--text) !important;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 30px rgba(0, 217, 255, 0.35) !important;
        }
        
        .btn-success {
            background: linear-gradient(45deg, var(--green), var(--cyber)) !important;
            border-color: var(--green) !important;
            color: var(--text) !important;
        }
        
        .btn-outline-success {
            background: transparent !important;
            border-color: var(--green) !important;
            color: var(--green) !important;
        }
        
        .btn-outline-success:hover {
            background: var(--green) !important;
            color: var(--text) !important;
        }
        
        .btn-warning {
            background: linear-gradient(45deg, var(--terracota), var(--magenta)) !important;
            border-color: var(--terracota) !important;
            color: var(--text) !important;
        }
        
        .btn-outline-warning {
            background: transparent !important;
            border-color: var(--terracota) !important;
            color: var(--terracota) !important;
        }
        
        .btn-outline-warning:hover {
            background: var(--terracota) !important;
            color: var(--text) !important;
        }
        
        .form-control, .form-select {
            border-radius: var(--radius) !important;
            border: 2px solid var(--border) !important;
            background: var(--card-bg) !important;
            color: var(--text) !important;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--cyber) !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 217, 255, 0.25) !important;
            background: var(--card-hover) !important;
        }
        
        .form-select option {
            background: var(--bg-1) !important;
            color: var(--text) !important;
        }
        
        .progress {
            height: 8px !important;
            border-radius: var(--radius) !important;
            background-color: var(--border) !important;
        }
        
        .progress-bar {
            border-radius: var(--radius) !important;
        }
        
        .progress-bar.bg-warning {
            background: linear-gradient(90deg, var(--terracota), var(--magenta)) !important;
        }
        
        .progress-bar.bg-success {
            background: linear-gradient(90deg, var(--green), var(--cyber)) !important;
        }
        
        .badge {
            padding: 0.5em 0.8em !important;
            border-radius: var(--radius) !important;
        }
        
        .badge.bg-warning {
            background: linear-gradient(45deg, var(--terracota), var(--magenta)) !important;
            color: var(--text) !important;
        }
        
        .badge.bg-success {
            background: linear-gradient(45deg, var(--green), var(--cyber)) !important;
            color: var(--text) !important;
        }
        
        .table {
            border-radius: var(--radius) !important;
            overflow: hidden !important;
            background: var(--card-bg) !important;
        }
        
        .table th {
            border-top: none !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            background: var(--bg-2) !important;
            color: var(--text) !important;
            border-bottom: 1px solid var(--border) !important;
        }
        
        .table td {
            color: var(--text) !important;
            border-bottom: 1px solid var(--border) !important;
        }
        
        .table.table-warning th {
            background: linear-gradient(45deg, var(--terracota), var(--magenta)) !important;
        }
        
        .table.table-success th {
            background: linear-gradient(45deg, var(--green), var(--cyber)) !important;
        }
        
        .alert {
            border: none !important;
            border-radius: var(--radius) !important;
            backdrop-filter: blur(10px) !important;
        }
        
        .alert-info {
            background: var(--card-bg) !important;
            border: 1px solid var(--cyber) !important;
            color: var(--text) !important;
        }
        
        .alert-warning {
            background: rgba(232, 93, 4, 0.1) !important;
            border: 1px solid var(--terracota) !important;
            color: var(--text) !important;
        }
        
        .alert-success {
            background: rgba(0, 200, 150, 0.1) !important;
            border: 1px solid var(--green) !important;
            color: var(--text) !important;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in !important;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .moneda-badge {
            font-size: 0.8rem !important;
            opacity: 0.8 !important;
            color: var(--text-muted) !important;
        }
        
        .fw-bold {
            font-weight: 700 !important;
            color: var(--text) !important;
        }
        
        .text-warning {
            color: var(--terracota) !important;
        }
        
        .text-success {
            color: var(--green) !important;
        }
        
        .text-primary {
            color: var(--cyber) !important;
        }
        
        .text-muted {
            color: var(--text-muted) !important;
        }
        
        option[style*="background-color: #fff3cd"] {
            background-color: var(--terracota) !important;
            color: var(--text) !important;
            font-weight: bold !important;
        }
        
        details summary {
            cursor: pointer !important;
            padding: 0.5rem !important;
            border-radius: var(--radius) !important;
            transition: var(--transition) !important;
            color: var(--text) !important;
        }
        
        details summary:hover {
            background: var(--card-hover) !important;
        }
        
        .btn:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
        }
        
        .btn.active-moneda {
            background: linear-gradient(45deg, var(--green), var(--cyber)) !important;
            border-color: var(--green) !important;
            color: var(--text) !important;
        }
        
        h5 {
            color: var(--text) !important;
            font-weight: 700 !important;
        }
        
        small {
            color: var(--text-muted) !important;
        }
        
        .bg-light {
            background: var(--card-bg) !important;
            border: 1px solid var(--border) !important;
        }
        
        /* Forzar estilos en elementos específicos */
        .input-group .btn {
            border-left: none !important;
        }
        
        .input-group .form-control {
            border-right: none !important;
        }
        
        .btn-group .btn {
            border-radius: 0 !important;
        }
        
        .btn-group .btn:first-child {
            border-radius: var(--radius) 0 0 var(--radius) !important;
        }
        
        .btn-group .btn:last-child {
            border-radius: 0 var(--radius) var(--radius) 0 !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Info Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> ¿Cómo funciona?</h5>
                    <p class="mb-0">
                        1. Selecciona un paquete de recarga → calcula comisión del 13%<br>
                        2. Avanza niveles Usuarios 1-12: Miztel 8 acumula comisiones<br>
                        3. Sube niveles Miztel 8→0: Cada nivel superior gana el DOBLE
                    </p>
                </div>
            </div>
        </div>

        <!-- Controles -->
        <div class="row mb-4">
            <!-- Selector Paquete -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-phone"></i> 1. Paquete de Recarga</h5>
                    </div>
                    <div class="card-body">
                        <select id="paqueteSelector" class="form-select form-select-lg">
                            <option value="" disabled selected>Selecciona un paquete...</option>
                            <?php
                            $paquetesRecarga = [
                                ['id' => '1D', 'nombre' => '1 día', 'vigencia' => '1 día', 'datos' => '0.5GB', 'precioMXN' => 25],
                                ['id' => 'RS', 'nombre' => 'RS ILIMITADA', 'vigencia' => '3 días', 'datos' => '2GB', 'precioMXN' => 50],
                                ['id' => '7D', 'nombre' => '7D ILIMITADA', 'vigencia' => '7 días', 'datos' => '2GB', 'precioMXN' => 60],
                                ['id' => '15D-5GB', 'nombre' => '15D ILIMITADA', 'vigencia' => '15 días', 'datos' => '5GB', 'precioMXN' => 110],
                                ['id' => '15D-10GB', 'nombre' => '15D ILIMITADA', 'vigencia' => '15 días', 'datos' => '10GB', 'precioMXN' => 140],
                                ['id' => '20D', 'nombre' => '20D ILIMITADA', 'vigencia' => '20 días', 'datos' => '8GB', 'precioMXN' => 160],
                                ['id' => '30D-1GB', 'nombre' => '30D', 'vigencia' => '30 días', 'datos' => '1GB', 'precioMXN' => 85],
                                ['id' => '30D-2GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '2GB', 'precioMXN' => 111],
                                ['id' => '30D-4GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '4GB', 'precioMXN' => 161],
                                ['id' => '30D-12GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '12GB', 'precioMXN' => 200],
                                ['id' => '30D-24GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '24GB', 'precioMXN' => 260],
                                ['id' => '30D-35GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '35GB', 'precioMXN' => 310],
                                ['id' => '30D-50GB', 'nombre' => '30D ILIMITADA', 'vigencia' => '30 días', 'datos' => '50GB', 'precioMXN' => 510],
                                ['id' => '12M-12GB', 'nombre' => '12M ILIMITADA', 'vigencia' => '360 días', 'datos' => '12GB', 'precioMXN' => 2210],
                                ['id' => '12M-24GB', 'nombre' => '12M ILIMITADA', 'vigencia' => '360 días', 'datos' => '24GB', 'precioMXN' => 2870],
                            ];
                            
                            foreach ($paquetesRecarga as $p): 
                                $precioUSD = round($p['precioMXN'] / $tasaCambioMXN, 2);
                                $highlightClass = (in_array($p['id'], ['30D-1GB', '30D-24GB'])) ? ' style="background-color: #fff3cd; font-weight: bold; color: #000;"' : '';
                            ?>
                            <option value="<?= $p['precioMXN'] ?>" data-usd="<?= $precioUSD ?>"<?= $highlightClass ?>>
                                <?= $p['nombre'] ?> (<?= $p['vigencia'] ?>, <?= $p['datos'] ?>) - $<?= $p['precioMXN'] ?> MXN (~$<?= number_format($precioUSD, 2) ?> USD)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="paqueteInfo" class="mt-3 p-3 bg-light rounded d-none">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">MXN</small>
                                    <div class="fw-bold text-primary" id="precioMXN">-</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">USD</small>
                                    <div class="fw-bold text-success" id="precioUSD">-</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Comisión 13%</small>
                                    <div class="fw-bold text-warning" id="comisionTotal">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selector Moneda -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-currency-exchange"></i> 2. Moneda</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="moneda" id="usdt" value="USDT" checked>
                            <label class="btn btn-success active-moneda" for="usdt" id="btnUsdt">USDT</label>
                            <input type="radio" class="btn-check" name="moneda" id="mxn" value="MXN">
                            <label class="btn btn-outline-success" for="mxn" id="btnMxn">MXN</label>
                        </div>
                        <small class="text-muted d-block mt-2">1 USD = $<?= number_format($tasaCambioMXN, 2) ?> MXN</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simulación 1: Usuarios -->
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-people"></i> Simulación 1: Avance Usuarios 1-12</h5>
            </div>
            <div class="card-body">
                <!-- Control + número focal -->
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center">
                        <label class="fw-bold d-block mb-2">Nivel de Usuarios</label>
                        <div class="input-group input-group-lg mb-2">
                            <button class="btn btn-warning btn-sim" id="btnUserMinus" onclick="cambiarNivelUsuario(-1)" disabled><i class="bi bi-dash-lg"></i></button>
                            <input type="number" id="nivelUsuario" class="form-control text-center fw-bold" value="0" min="0" max="12" readonly>
                            <button class="btn btn-warning btn-sim" id="btnUserPlus" onclick="cambiarNivelUsuario(1)" disabled><i class="bi bi-plus-lg"></i></button>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="progressUsuarios" class="progress-bar bg-warning" style="width: 0%"></div>
                        </div>
                        <small class="text-muted" id="nombreNivelUsuario">Inicio</small>
                        <div class="mt-2 small">
                            <span class="text-muted">Líneas este nivel:</span> <strong id="lineasNivelUsuario" class="text-warning">-</strong><br>
                            <span class="text-muted">Líneas acumuladas:</span> <strong id="lineasTotalUsuario" class="text-dark">-</strong>
                        </div>
                    </div>
                    <div class="col-md-8 text-center">
                        <div class="text-muted text-uppercase small">Cada lugar de Miztel 8 recibe</div>
                        <div class="fw-bold text-warning lh-1 my-1" style="font-size: 3rem;">
                            <span id="comisionUsuario">$0.00</span>
                            <span class="fs-6 text-muted moneda-badge align-middle">USDT</span>
                        </div>
                        <div class="text-muted small">mensualmente</div>
                        <span id="deltaUsuario" class="badge bg-success-subtle text-success-emphasis fs-6 d-none">
                            <i class="bi bi-arrow-up"></i> +$0.00 con este nivel
                        </span>
                        <div id="hitoUsuarios" class="alert alert-warning py-2 mt-3 mb-0 d-none">
                            <div class="fw-bold text-warning fs-5">🎯 Hito Alcanzado</div>
                            <div class="text-dark">Mes <span id="hitoMes">-</span> - <span id="hitoLineas">-</span> Líneas activas</div>
                        </div>
                    </div>
                </div>

                <!-- Tabla detallada (opcional) -->
                <details class="mt-3">
                    <summary class="text-muted small" style="cursor:pointer;">Ver tabla detallada por nivel</summary>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr><th>Nivel</th><th>Lugares</th><th>Comisión Nivel</th><th>Acumulado x1</th><th class="bg-warning text-dark">Total x256 (Miztel 8)</th></tr>
                            </thead>
                            <tbody id="tablaUsuarios"></tbody>
                        </table>
                    </div>
                </details>
            </div>
        </div>

        <!-- Simulación 2: Miztel -->
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Simulación 2: Ascenso Miztel 8→0</h5>
            </div>
            <div class="card-body">
                <!-- Control + número focal -->
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center">
                        <label class="fw-bold d-block mb-2">Nivel Miztel</label>
                        <div class="input-group input-group-lg mb-2">
                            <button class="btn btn-outline-success btn-sim" id="btnMiztelDown" onclick="cambiarNivelMiztel(-1)" title="Bajar" disabled><i class="bi bi-arrow-down-lg"></i></button>
                            <input type="text" id="nivelMiztel" class="form-control text-center fw-bold" value="Miztel 8" readonly>
                            <button class="btn btn-success btn-sim" id="btnMiztelUp" onclick="cambiarNivelMiztel(1)" title="Ascender" disabled><i class="bi bi-arrow-up-lg"></i></button>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="progressMiztel" class="progress-bar bg-success" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Multiplicador actual: <span class="badge bg-success">x<span id="multiplicador">1</span></span></small>
                    </div>
                    <div class="col-md-8 text-center">
                        <div class="text-muted text-uppercase small">Ganancia por lugar en <span id="nombreNivelMiztel" class="fw-bold text-success">Miztel 8</span></div>
                        <div class="fw-bold text-success lh-1 my-1" style="font-size: 3rem;">
                            <span id="gananciaMiztel">$0.00</span>
                            <span class="fs-6 text-muted moneda-badge align-middle">USDT</span>
                        </div>
                        <span id="deltaMiztel" class="badge bg-success-subtle text-success-emphasis fs-6 d-none">
                            <i class="bi bi-arrow-up"></i> el doble que el nivel anterior
                        </span>
                        <div class="text-muted small mt-2">
                            Inversión requerida: <strong id="tokensRequeridos" class="text-dark">1 token</strong> 
                            (<strong id="inversionMiztel">$100.00</strong> <span class="moneda-badge">USDT</span>)
                        </div>
                    </div>
                </div>

                <!-- Tabla detallada (opcional) -->
                <details class="mt-3">
                    <summary class="text-muted small" style="cursor:pointer;">Ver tabla detallada por nivel Miztel</summary>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered">
                            <thead class="table-dark">
                                <tr><th>Nivel</th><th>Lugares</th><th>Tokens Req</th><th>Aportación</th><th class="bg-success">Multiplicador</th><th class="bg-success">Ganancia/Lugar</th><th class="bg-success">Ganancia Total</th></tr>
                            </thead>
                            <tbody id="tablaMiztel"></tbody>
                        </table>
                    </div>
                </details>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calculator-fill"></i> Resumen</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Paquete</small>
                            <div class="fw-bold" id="resumenPaquete">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Comisión Total</small>
                            <div class="fw-bold text-warning" id="resumenComision">$0.00</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Nivel Miztel</small>
                            <div class="fw-bold text-success" id="resumenNivel">Miztel 8</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-2 border rounded">
                            <small class="text-muted">Ganancia Total Estimada</small>
                            <div class="fw-bold text-primary" id="resumenTotal">$0.00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const tasaMXN = <?= $tasaCambioMXN ?>;
        
        // Niveles Miztel 0-8
        const nivelesMiztel = [
            ['nivel' => 'Miztel 0', 'ingreso' => 67339.49, 'lugares' => 1, 'aportacion' => 25600, 'tokensReq' => 256],
            ['nivel' => 'Miztel 1', 'ingreso' => 33669.71, 'lugares' => 2, 'aportacion' => 12800, 'tokensReq' => 128],
            ['nivel' => 'Miztel 2', 'ingreso' => 16834.82, 'lugares' => 4, 'aportacion' => 6400, 'tokensReq' => 64],
            ['nivel' => 'Miztel 3', 'ingreso' => 8417.38, 'lugares' => 8, 'aportacion' => 3200, 'tokensReq' => 32],
            ['nivel' => 'Miztel 4', 'ingreso' => 4208.66, 'lugares' => 16, 'aportacion' => 1600, 'tokensReq' => 16],
            ['nivel' => 'Miztel 5', 'ingreso' => 2104.30, 'lugares' => 32, 'aportacion' => 800, 'tokensReq' => 8],
            ['nivel' => 'Miztel 6', 'ingreso' => 1052.12, 'lugares' => 64, 'aportacion' => 400, 'tokensReq' => 4],
            ['nivel' => 'Miztel 7', 'ingreso' => 526.03, 'lugares' => 128, 'aportacion' => 200, 'tokensReq' => 2],
            ['nivel' => 'Miztel 8', 'ingreso' => 262.98, 'lugares' => 256, 'aportacion' => 100, 'tokensReq' => 1],
        ];

        // Niveles Usuarios 1-12
        const nivelesUsuarios = [
            ['nivel' => 'Usuarios 1', 'lugares' => 512],
            ['nivel' => 'Usuarios 2', 'lugares' => 1024],
            ['nivel' => 'Usuarios 3', 'lugares' => 2048],
            ['nivel' => 'Usuarios 4', 'lugares' => 4096],
            ['nivel' => 'Usuarios 5', 'lugares' => 8192],
            ['nivel' => 'Usuarios 6', 'lugares' => 16384],
            ['nivel' => 'Usuarios 7', 'lugares' => 32768],
            ['nivel' => 'Usuarios 8', 'lugares' => 65536],
            ['nivel' => 'Usuarios 9', 'lugares' => 131072],
            ['nivel' => 'Usuarios 10', 'lugares' => 262144],
            ['nivel' => 'Usuarios 11', 'lugares' => 524288],
            ['nivel' => 'Usuarios 12', 'lugares' => 1048576],
        ];

        // Tabla de comisiones L4-L24 (21 niveles: 0-20)
        const tablaComisiones = [0.064, 0.064, 0.128, 0.257, 0.514, 1.028, 2.055, 4.110, 8.220, 16.440, 32.881, 65.761, 131.523, 263.045, 526.090, 1052.180, 2104.361, 4208.722, 8417.444, 16834.888, 33669.775];

        let precioUSD = 0, comisionTotal = 0, nivelUsuario = 0, nivelMiztelActual = 8, moneda = 'USDT';
        const LUGARES_MIZTEL8 = 256; // Miztel 8 tiene 256 lugares

        // Mapeo de hitos: nivelUsuario -> {mes, lineasActivas}
        const hitosUsuarios = {
            1: { mes: 1, lineas: 512 },
            2: { mes: 2, lineas: 1536 },
            3: { mes: 3, lineas: 3584 },
            4: { mes: 4, lineas: 7680 },
            5: { mes: 6, lineas: 15872 },
            6: { mes: 8, lineas: 32256 },
            7: { mes: 10, lineas: 65024 },
            8: { mes: 12, lineas: 130560 },
            9: { mes: 18, lineas: 261632 },
            10: { mes: 24, lineas: 523776 },
            11: { mes: 30, lineas: 1048064 },
            12: { mes: 36, lineas: 2096640 }
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('paqueteSelector').addEventListener('change', seleccionarPaquete);
            document.querySelectorAll('input[name="moneda"]').forEach(r => r.addEventListener('change', (e) => {
                moneda = e.target.value;
                document.querySelectorAll('.moneda-badge').forEach(b => b.textContent = moneda);
                // Actualizar estilos de botones de moneda
                const btnUsdt = document.getElementById('btnUsdt');
                const btnMxn = document.getElementById('btnMxn');
                if (moneda === 'USDT') {
                    btnUsdt.classList.remove('btn-outline-success');
                    btnUsdt.classList.add('btn-success');
                    btnMxn.classList.remove('btn-success');
                    btnMxn.classList.add('btn-outline-success');
                } else {
                    btnMxn.classList.remove('btn-outline-success');
                    btnMxn.classList.add('btn-success');
                    btnUsdt.classList.remove('btn-success');
                    btnUsdt.classList.add('btn-outline-success');
                }
                actualizarTodo();
            }));
            renderizarTablasInicial();
        });

        function format(valor) {
            return '$' + (moneda === 'MXN' ? (valor * tasaMXN).toFixed(2) : valor.toFixed(2));
        }

        function seleccionarPaquete(e) {
            const precioMXN = parseFloat(e.target.value);
            precioUSD = parseFloat(e.target.options[e.target.selectedIndex].dataset.usd);
            comisionTotal = precioUSD * 0.13;
            
            document.getElementById('paqueteInfo').classList.remove('d-none');
            document.getElementById('precioMXN').textContent = '$' + precioMXN.toFixed(2);
            document.getElementById('precioUSD').textContent = '$' + precioUSD.toFixed(2);
            document.getElementById('comisionTotal').textContent = '$' + comisionTotal.toFixed(2);
            document.getElementById('resumenPaquete').textContent = e.target.options[e.target.selectedIndex].text.split(' -')[0];
            
            // Habilitar botones de simuladores
            document.querySelectorAll('.btn-sim').forEach(btn => btn.disabled = false);
            
            actualizarTodo();
        }

        function cambiarNivelUsuario(dir) {
            nivelUsuario = Math.max(0, Math.min(12, nivelUsuario + dir));
            document.getElementById('nivelUsuario').value = nivelUsuario;
            actualizarTodo();
        }

        function cambiarNivelMiztel(dir) {
            // Avanzar (dir=1) significa subir de Miztel 8 -> 7 -> 0 (número baja, multiplicador sube)
            nivelMiztelActual = Math.max(0, Math.min(8, nivelMiztelActual - dir));
            document.getElementById('nivelMiztel').value = nivelesMiztel[nivelMiztelActual].nivel;
            actualizarTodo();
        }

        // Comisión total a repartir dividida en 20 niveles
        function comisionPorNivel() {
            return comisionTotal / 20;
        }

        // Comisión del nivel de red n (L[n]): (comisionTotal/20) * lugares del nivel (2^n)
        // L1=0.032*2=0.064, L2=0.032*4=0.128, L3=0.032*8=0.256 ...
        function comisionNivelRed(n) {
            if (!comisionTotal || n < 1) return 0;
            return comisionPorNivel() * Math.pow(2, n);
        }

        // Comisión acumulada que recibe CADA lugar de Miztel 8 (C12) al nivel de Usuarios indicado
        // Usuarios 1 = L1; Usuarios 2 = L1+L2; Usuarios 3 = L1+L2+L3 ...
        function calcularComisionAcumulada(nivelUsuario) {
            if (!comisionTotal || nivelUsuario === 0) return 0;
            let acum = 0;
            for (let k = 1; k <= nivelUsuario; k++) {
                acum += comisionNivelRed(k);
            }
            return acum;
        }

        // Calcular líneas del nivel de usuarios actual (sin modificar fórmulas existentes)
        function calcularLineasNivelUsuario(nivel) {
            if (nivel === 0) return 0;
            return nivelesUsuarios[nivel - 1]?.lugares || 0;
        }

        // Calcular líneas acumuladas hasta el nivel de usuarios indicado
        function calcularLineasTotalUsuario(nivel) {
            if (nivel === 0) return 0;
            let total = 0;
            for (let k = 1; k <= nivel; k++) {
                total += calcularLineasNivelUsuario(k);
            }
            return total;
        }

        function renderizarTablasInicial() {
            // Tabla Usuarios inicial (vacía, esperando paquete)
            const tbUsuario = document.getElementById('tablaUsuarios');
            tbUsuario.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-arrow-up-circle"></i> Selecciona un paquete arriba para ver los cálculos</td></tr>';
            
            // Tabla Miztel inicial (vacía, esperando paquete)
            const tbMiztel = document.getElementById('tablaMiztel');
            tbMiztel.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3"><i class="bi bi-arrow-up-circle"></i> Selecciona un paquete arriba para ver los cálculos</td></tr>';
        }

        function actualizarTablaUsuarios() {
            const tbUsuario = document.getElementById('tablaUsuarios');
            if (!comisionTotal) {
                tbUsuario.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Selecciona un paquete para ver los cálculos</td></tr>';
                return;
            }
            
            tbUsuario.innerHTML = '<tr id="row-u0"><td class="fw-bold">Inicio</td><td>-</td><td>-</td><td>-</td><td class="bg-warning">' + format(0) + '</td></tr>';
            
            nivelesUsuarios.forEach((n, i) => {
                const nivel = i + 1;
                const comisionNivel = comisionNivelRed(nivel);
                const acum = calcularComisionAcumulada(nivel);
                const esActivo = nivel <= nivelUsuario;
                const esActual = nivel === nivelUsuario;
                
                tbUsuario.innerHTML += '<tr id="row-u' + nivel + '" style="opacity:' + (esActivo ? '1' : '0.5') + '" class="' + (esActual ? 'table-warning' : '') + '">' +
                    '<td class="fw-bold">' + n.nivel + '</td>' +
                    '<td>' + n.lugares.toLocaleString() + '</td>' +
                    '<td>' + format(comisionNivel) + '</td>' +
                    '<td class="fw-bold">' + format(acum) + '</td>' +
                    '<td class="bg-warning fw-bold">' + format(acum * LUGARES_MIZTEL8) + '</td>' +
                '</tr>';
            });
        }

        function actualizarTablaMiztel() {
            const tbMiztel = document.getElementById('tablaMiztel');
            if (!comisionTotal) {
                tbMiztel.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Selecciona un paquete para ver los cálculos</td></tr>';
                return;
            }
            
            tbMiztel.innerHTML = '';
            
            // baseMiztel8 = comisión que recibe CADA lugar de Miztel 8 (C12). NO se divide entre 256.
            const baseMiztel8 = calcularComisionAcumulada(nivelUsuario);
            
            // Recorrer niveles de Miztel 8 a 0
            nivelesMiztel.slice().reverse().forEach((n, i) => {
                const nivelMiztel = 8 - i; // 8, 7, 6, 5, 4, 3, 2, 1, 0
                const mult = Math.pow(2, i); // Miztel8=1, Miztel7=2, Miztel6=4 ... Miztel0=256
                const gananciaPorLugar = baseMiztel8 * mult; // cada nivel superior gana el doble
                const gananciaTotal = gananciaPorLugar * n.lugares;
                
                const esActivo = nivelMiztel >= nivelMiztelActual;
                const esActual = nivelMiztel === nivelMiztelActual;
                
                tbMiztel.innerHTML += '<tr style="opacity:' + (esActivo ? '1' : '0.5') + '" class="' + (esActual ? 'table-success' : '') + '">' +
                    '<td class="fw-bold">' + n.nivel + '</td>' +
                    '<td>' + n.lugares.toLocaleString() + '</td>' +
                    '<td>' + n.tokensReq + '</td>' +
                    '<td>$' + n.aportacion.toLocaleString() + '</td>' +
                    '<td class="bg-success text-white">x' + mult + '</td>' +
                    '<td class="fw-bold">' + format(gananciaPorLugar) + '</td>' +
                    '<td class="bg-success fw-bold">' + format(gananciaTotal) + '</td>' +
                '</tr>';
            });
        }

        function actualizarTodo() {
            if (!comisionTotal) return;
            
            // === SIMULACIÓN 1: USUARIOS ===
            const porcU = (nivelUsuario / 12) * 100;
            document.getElementById('progressUsuarios').style.width = porcU + '%';
            document.getElementById('nombreNivelUsuario').textContent = nivelUsuario === 0 ? 'Inicio (sin usuarios)' : nivelesUsuarios[nivelUsuario - 1].nivel + ' / 12';
            
            // Actualizar líneas de usuarios (nuevo, sin modificar fórmulas existentes)
            const lineasNivel = calcularLineasNivelUsuario(nivelUsuario);
            const lineasTotal = calcularLineasTotalUsuario(nivelUsuario);
            document.getElementById('lineasNivelUsuario').textContent = nivelUsuario === 0 ? '-' : lineasNivel.toLocaleString();
            document.getElementById('lineasTotalUsuario').textContent = nivelUsuario === 0 ? '-' : lineasTotal.toLocaleString();
            
            const comUser = calcularComisionAcumulada(nivelUsuario);
            document.getElementById('comisionUsuario').textContent = format(comUser);
            
            // Actualizar hito de usuarios
            const hitoEl = document.getElementById('hitoUsuarios');
            if (nivelUsuario > 0 && hitosUsuarios[nivelUsuario]) {
                hitoEl.classList.remove('d-none');
                // Cambiar a verde si es Mes 36 (nivel 12)
                if (nivelUsuario === 12) {
                    hitoEl.classList.remove('alert-warning');
                    hitoEl.classList.add('alert-success');
                } else {
                    hitoEl.classList.remove('alert-success');
                    hitoEl.classList.add('alert-warning');
                }
                document.getElementById('hitoMes').textContent = hitosUsuarios[nivelUsuario].mes;
                document.getElementById('hitoLineas').textContent = hitosUsuarios[nivelUsuario].lineas.toLocaleString();
            } else {
                hitoEl.classList.add('d-none');
            }
            
            // Delta: impacto de este último movimiento = comisión del nivel recién alcanzado (L[n])
            const deltaUser = document.getElementById('deltaUsuario');
            if (nivelUsuario > 0) {
                deltaUser.classList.remove('d-none');
                deltaUser.innerHTML = '<i class="bi bi-arrow-up"></i> +' + format(comisionNivelRed(nivelUsuario)) + ' al pasar a este nivel';
            } else {
                deltaUser.classList.add('d-none');
            }
            
            // Actualizar tabla de usuarios con valores correctos
            actualizarTablaUsuarios();
            
            // === SIMULACIÓN 2: MIZTEL ===
            const nivelesAvanzados = 8 - nivelMiztelActual; // 0 en Miztel 8, 8 en Miztel 0
            const porcM = (nivelesAvanzados / 8) * 100;
            document.getElementById('progressMiztel').style.width = porcM + '%';
            document.getElementById('nombreNivelMiztel').textContent = nivelesMiztel[nivelMiztelActual].nivel;
            
            // Multiplicador: Miztel 8=×1, Miztel 7=×2, Miztel 6=×4 ... Miztel 0=×256
            const mult = Math.pow(2, nivelesAvanzados);
            document.getElementById('multiplicador').textContent = mult;
            
            // Ganancia por lugar = comisión acumulada de Miztel 8 (C12) × multiplicador
            const gananciaPorLugar = comUser * mult;
            document.getElementById('gananciaMiztel').textContent = format(gananciaPorLugar);
            
            // Inversión requerida para este nivel Miztel
            const tokensReq = nivelesMiztel[nivelMiztelActual].tokensReq;
            const aportacion = nivelesMiztel[nivelMiztelActual].aportacion;
            document.getElementById('tokensRequeridos').textContent = tokensReq + ' token' + (tokensReq > 1 ? 's' : '');
            // Aplicar conversión de moneda (USDT a MXN si es necesario)
            const aportacionConvertida = moneda === 'MXN' ? (aportacion * tasaMXN) : aportacion;
            document.getElementById('inversionMiztel').textContent = '$' + aportacionConvertida.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Delta: cada ascenso duplica la ganancia por lugar
            const deltaMiz = document.getElementById('deltaMiztel');
            if (nivelesAvanzados > 0) {
                deltaMiz.classList.remove('d-none');
                deltaMiz.innerHTML = '<i class="bi bi-arrow-up"></i> el doble que ' + nivelesMiztel[nivelMiztelActual + 1].nivel + ' (+' + format(gananciaPorLugar / 2) + ')';
            } else {
                deltaMiz.classList.add('d-none');
            }
            
            // Actualizar tabla Miztel
            actualizarTablaMiztel();
            
            // === RESUMEN ===
            document.getElementById('resumenComision').textContent = format(comisionTotal);
            document.getElementById('resumenNivel').textContent = nivelesMiztel[nivelMiztelActual].nivel;
            document.getElementById('resumenTotal').textContent = format(gananciaPorLugar * nivelesMiztel[nivelMiztelActual].lugares);
        }
    </script>
</body>
</html>
