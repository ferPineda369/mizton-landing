<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación simple
$isAuthenticated = isset($_SESSION['sorteo_admin']) && $_SESSION['sorteo_admin'] === true;

if (!$isAuthenticated && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === '1284') {
        $_SESSION['sorteo_admin'] = true;
        $isAuthenticated = true;
    } else {
        $error = 'Contraseña incorrecta';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!$isAuthenticated) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sorteo Mizton</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Acceso Administrativo</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
exit;
}

// Obtener estadísticas
try {
    cleanExpiredReservations($pdo);
    
    $statsSql = "SELECT 
                    status,
                    COUNT(*) as count
                 FROM sorteo_numbers 
                 GROUP BY status";
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute();
    $stats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Obtener números reservados y confirmados
    $numbersSql = "SELECT * FROM sorteo_numbers 
                   WHERE status IN ('reserved', 'confirmed') 
                   ORDER BY number_value ASC";
    $numbersStmt = $pdo->prepare($numbersSql);
    $numbersStmt->execute();
    $numbers = $numbersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener transacciones recientes
    $transactionsSql = "SELECT * FROM sorteo_transactions 
                        ORDER BY created_at DESC 
                        LIMIT 20";
    $transactionsStmt = $pdo->prepare($transactionsSql);
    $transactionsStmt->execute();
    $transactions = $transactionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Sorteo Mizton</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #2E8B57 0%, #3CB371 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .number-badge {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border-radius: 8px;
            font-weight: bold;
            margin: 2px;
        }
        .badge-available { background: #28a745; color: white; }
        .badge-reserved { background: #ffc107; color: #212529; }
        .badge-confirmed { background: #dc3545; color: white; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-success">
        <div class="container">
            <span class="navbar-brand">Panel Administrativo - Sorteo Mizton</span>
            <a href="?logout=1" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['available'] ?? 0; ?></h3>
                    <p>Disponibles</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['reserved'] ?? 0; ?></h3>
                    <p>Reservados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <h3><?php echo $stats['confirmed'] ?? 0; ?></h3>
                    <p>Confirmados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <h3><?php echo ($stats['confirmed'] ?? 0) * 50; ?></h3>
                    <p>Total Recaudado ($)</p>
                </div>
            </div>
        </div>

        <!-- Números Ocupados -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Números Reservados y Confirmados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($numbers)): ?>
                            <p class="text-muted">No hay números reservados o confirmados</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Estado</th>
                                            <th>Participante</th>
                                            <th>Email</th>
                                            <th>Fecha Reserva</th>
                                            <th>Expira</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($numbers as $number): ?>
                                            <tr>
                                                <td>
                                                    <span class="number-badge badge-<?php echo $number['status']; ?>">
                                                        <?php echo $number['number_value']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($number['status'] === 'reserved'): ?>
                                                        <span class="badge bg-warning">Reservado</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Confirmado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($number['participant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($number['participant_email']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($number['reserved_at'])); ?></td>
                                                <td>
                                                    <?php if ($number['reservation_expires_at']): ?>
                                                        <?php echo date('d/m/Y H:i', strtotime($number['reservation_expires_at'])); ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($number['status'] === 'reserved'): ?>
                                                        <button class="btn btn-sm btn-success" 
                                                                onclick="confirmPayment(<?php echo $number['number_value']; ?>)">
                                                            Confirmar Pago
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-success">✓ Pagado</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transacciones Recientes -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Transacciones Recientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <p class="text-muted">No hay transacciones registradas</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Número</th>
                                            <th>Participante</th>
                                            <th>Acción</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                                                <td><?php echo $transaction['number_value']; ?></td>
                                                <td><?php echo htmlspecialchars($transaction['participant_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $actionColors = [
                                                        'reserved' => 'warning',
                                                        'confirmed' => 'success',
                                                        'expired' => 'secondary',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $actionColors[$transaction['action']] ?? 'primary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo ucfirst($transaction['action']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $transaction['ip_address']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmPayment(number) {
            if (confirm(`¿Confirmar el pago del número ${number}?`)) {
                fetch('../api/confirm_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        number: number,
                        admin_key: 'mizton_sorteo_2025'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pago confirmado exitosamente');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                    console.error('Error:', error);
                });
            }
        }

        // Auto-refresh cada 30 segundos
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
