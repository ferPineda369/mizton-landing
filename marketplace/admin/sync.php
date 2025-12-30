<?php
/**
 * Panel Admin del Marketplace - Sincronización de Proyectos
 */

require_once __DIR__ . '/auth-admin.php';
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/sync-functions.php';

$db = getMarketplaceDB();

// Obtener proyectos con sincronización automática
$stmt = $db->query("
    SELECT 
        p.*,
        (SELECT COUNT(*) FROM tbl_marketplace_sync_log WHERE project_id = p.id AND status = 'failed' AND sync_timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as failed_24h
    FROM tbl_marketplace_projects p
    WHERE p.update_method != 'manual'
    ORDER BY p.last_sync_attempt DESC
");
$syncProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener últimos logs de sincronización
$stmt = $db->query("
    SELECT 
        l.*,
        p.project_code,
        p.name as project_name
    FROM tbl_marketplace_sync_log l
    JOIN tbl_marketplace_projects p ON l.project_id = p.id
    ORDER BY l.sync_timestamp DESC
    LIMIT 50
");
$syncLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Sincronización - Marketplace Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Mizton</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/marketplace/assets/css/marketplace.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fa;
            color: #2d3748;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1B4332 0%, #40916C 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 24px;
            font-weight: 700;
        }
        
        .admin-nav {
            display: flex;
            gap: 20px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .admin-nav a.active {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #40916C;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1B4332;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(64, 145, 108, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f7fafc;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 16px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tbody tr:hover {
            background: #f7fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-warning {
            background: #feebc8;
            color: #7c2d12;
        }
        
        .badge-info {
            background: #bee3f8;
            color: #2c5282;
        }
        
        .badge-danger {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .badge-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #3182ce;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }
        
        .sync-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sync-status i {
            font-size: 16px;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #e2e8f0;
            border-top-color: #40916C;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .log-details {
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }
        
        .error-message {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
            font-size: 12px;
            color: #742a2a;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-arrow-repeat"></i> Sincronización</h1>
            <nav class="admin-nav">
                <a href="/marketplace/admin/">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="/marketplace/admin/projects.php">
                    <i class="bi bi-folder"></i> Proyectos
                </a>
                <a href="/marketplace/admin/sync.php" class="active">
                    <i class="bi bi-arrow-repeat"></i> Sincronización
                </a>
                <a href="/marketplace/">
                    <i class="bi bi-eye"></i> Ver Marketplace
                </a>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill" style="font-size: 20px;"></i>
            <div>
                <strong>Sistema de Sincronización</strong>
                <p style="margin: 4px 0 0 0;">Los proyectos con método de actualización automático (API, Webhook, Blockchain) se sincronizan según su frecuencia configurada. El cron job se ejecuta cada 5 minutos.</p>
            </div>
        </div>

        <!-- Proyectos con Sincronización Automática -->
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-cloud-arrow-down"></i> Proyectos con Sincronización Automática</h2>
                <button onclick="syncAllProjects()" class="btn btn-primary" id="syncAllBtn">
                    <i class="bi bi-arrow-repeat"></i> Sincronizar Todos
                </button>
            </div>

            <?php if (empty($syncProjects)): ?>
            <p style="text-align: center; padding: 40px; color: #a0aec0;">
                <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 16px;"></i>
                No hay proyectos configurados con sincronización automática
            </p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Proyecto</th>
                        <th>Método</th>
                        <th>Frecuencia</th>
                        <th>Estado</th>
                        <th>Última Sync</th>
                        <th>Errores 24h</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($syncProjects as $proj): 
                        $updateMethodInfo = $UPDATE_METHODS[$proj['update_method']] ?? $UPDATE_METHODS['manual'];
                        $lastSync = $proj['last_successful_sync'] ? date('d/m/Y H:i', strtotime($proj['last_successful_sync'])) : 'Nunca';
                        
                        $statusBadge = 'secondary';
                        $statusText = 'Sin sincronizar';
                        if ($proj['sync_status'] === 'success') {
                            $statusBadge = 'success';
                            $statusText = 'Exitosa';
                        } elseif ($proj['sync_status'] === 'failed') {
                            $statusBadge = 'danger';
                            $statusText = 'Fallida';
                        } elseif ($proj['sync_status'] === 'partial') {
                            $statusBadge = 'warning';
                            $statusText = 'Parcial';
                        }
                    ?>
                    <tr data-project-code="<?php echo htmlspecialchars($proj['project_code']); ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($proj['project_code']); ?></strong>
                            <div class="log-details"><?php echo htmlspecialchars($proj['name']); ?></div>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $updateMethodInfo['label']; ?>
                            </span>
                        </td>
                        <td><?php echo $proj['update_frequency'] ? $proj['update_frequency'] . ' min' : '-'; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $statusBadge; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td><?php echo $lastSync; ?></td>
                        <td>
                            <?php if ($proj['failed_24h'] > 0): ?>
                                <span class="badge badge-danger"><?php echo $proj['failed_24h']; ?> errores</span>
                            <?php else: ?>
                                <span style="color: #a0aec0;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="syncProject('<?php echo htmlspecialchars($proj['project_code']); ?>')" 
                                    class="btn btn-primary btn-sm sync-btn">
                                <i class="bi bi-arrow-repeat"></i> Sincronizar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Historial de Sincronización -->
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-clock-history"></i> Historial de Sincronización (Últimas 50)</h2>
            </div>

            <?php if (empty($syncLogs)): ?>
            <p style="text-align: center; padding: 40px; color: #a0aec0;">
                No hay registros de sincronización
            </p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Proyecto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Tiempo</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($syncLogs as $log): 
                        $statusBadge = $log['status'] === 'success' ? 'success' : ($log['status'] === 'partial' ? 'warning' : 'danger');
                        $statusText = $log['status'] === 'success' ? 'Exitosa' : ($log['status'] === 'partial' ? 'Parcial' : 'Fallida');
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['sync_timestamp'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($log['project_code']); ?></strong>
                            <div class="log-details"><?php echo htmlspecialchars($log['project_name']); ?></div>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                <?php echo strtoupper($log['sync_method']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $statusBadge; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td><?php echo $log['execution_time_ms'] ? $log['execution_time_ms'] . ' ms' : '-'; ?></td>
                        <td>
                            <?php if ($log['error_message']): ?>
                                <div class="error-message">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <?php echo htmlspecialchars($log['error_message']); ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #48bb78;">
                                    <i class="bi bi-check-circle"></i> Sincronización exitosa
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function syncProject(projectCode) {
            const btn = document.querySelector(`tr[data-project-code="${projectCode}"] .sync-btn`);
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Sincronizando...';
            
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('project_code', projectCode);
            
            fetch('/marketplace/admin/api/sync-project.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sincronización completada exitosamente');
                    window.location.reload();
                } else {
                    alert('Error en la sincronización: ' + (data.message || 'Error desconocido'));
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al sincronizar el proyecto');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        }
        
        function syncAllProjects() {
            const btn = document.getElementById('syncAllBtn');
            const originalContent = btn.innerHTML;
            
            if (!confirm('¿Deseas sincronizar todos los proyectos con sincronización automática?')) {
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Sincronizando todos...';
            
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('sync_all', '1');
            
            fetch('/marketplace/admin/api/sync-project.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Sincronización completada:\n\nExitosas: ${data.success_count}\nFallidas: ${data.failed_count}`);
                    window.location.reload();
                } else {
                    alert('Error en la sincronización: ' + (data.message || 'Error desconocido'));
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al sincronizar los proyectos');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        }
    </script>
</body>
</html>
