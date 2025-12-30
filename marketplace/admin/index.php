<?php
/**
 * Panel Admin del Marketplace - Dashboard Principal
 */

require_once __DIR__ . '/auth-admin.php';
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';

// Obtener estadísticas generales
$db = getMarketplaceDB();

// Total de proyectos por estado
$stmt = $db->query("
    SELECT status, COUNT(*) as count 
    FROM tbl_marketplace_projects 
    GROUP BY status
");
$projectsByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Total de proyectos por categoría
$stmt = $db->query("
    SELECT category, COUNT(*) as count 
    FROM tbl_marketplace_projects 
    GROUP BY category 
    ORDER BY count DESC 
    LIMIT 10
");
$projectsByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proyectos destacados
$stmt = $db->query("
    SELECT COUNT(*) as count 
    FROM tbl_marketplace_projects 
    WHERE featured = TRUE
");
$featuredCount = $stmt->fetchColumn();

// Estadísticas de sincronización
$stmt = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN sync_status = 'success' THEN 1 ELSE 0 END) as success,
        SUM(CASE WHEN sync_status = 'failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN sync_status = 'never' THEN 1 ELSE 0 END) as never
    FROM tbl_marketplace_projects
    WHERE update_method != 'manual'
");
$syncStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Proyectos recientes (últimos 10)
$stmt = $db->query("
    SELECT 
        project_code,
        name,
        category,
        status,
        created_at,
        featured
    FROM tbl_marketplace_projects 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recentProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total general
$totalProjects = $db->query("SELECT COUNT(*) FROM tbl_marketplace_projects")->fetchColumn();
$totalDocuments = $db->query("SELECT COUNT(*) FROM tbl_marketplace_documents")->fetchColumn();
$totalMilestones = $db->query("SELECT COUNT(*) FROM tbl_marketplace_milestones")->fetchColumn();

$pageTitle = 'Dashboard Admin - Marketplace';
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        
        .stat-card.primary .icon {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .stat-card.success .icon {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .stat-card.warning .icon {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .stat-card.info .icon {
            background: #e1f5fe;
            color: #0288d1;
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #718096;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
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
        
        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .chart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .chart-item:last-child {
            border-bottom: none;
        }
        
        .chart-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .chart-value {
            font-weight: 700;
            color: #1a202c;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-speedometer2"></i> Marketplace Admin</h1>
            <nav class="admin-nav">
                <a href="/marketplace/admin/" class="active">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="/marketplace/admin/projects.php">
                    <i class="bi bi-folder"></i> Proyectos
                </a>
                <a href="/marketplace/admin/sync.php">
                    <i class="bi bi-arrow-repeat"></i> Sincronización
                </a>
                <a href="/marketplace/">
                    <i class="bi bi-eye"></i> Ver Marketplace
                </a>
                <a href="https://panel.mizton.cat/">
                    <i class="bi bi-box-arrow-left"></i> Panel Principal
                </a>
            </nav>
        </div>
    </div>

    <div class="container">
        <!-- Estadísticas Principales -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="icon">
                    <i class="bi bi-folder"></i>
                </div>
                <h3>Total Proyectos</h3>
                <div class="value"><?php echo number_format($totalProjects); ?></div>
            </div>
            
            <div class="stat-card success">
                <div class="icon">
                    <i class="bi bi-star"></i>
                </div>
                <h3>Proyectos Destacados</h3>
                <div class="value"><?php echo number_format($featuredCount); ?></div>
            </div>
            
            <div class="stat-card warning">
                <div class="icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <h3>Total Documentos</h3>
                <div class="value"><?php echo number_format($totalDocuments); ?></div>
            </div>
            
            <div class="stat-card info">
                <div class="icon">
                    <i class="bi bi-flag"></i>
                </div>
                <h3>Total Milestones</h3>
                <div class="value"><?php echo number_format($totalMilestones); ?></div>
            </div>
        </div>

        <!-- Proyectos por Estado y Categoría -->
        <div class="chart-container">
            <div class="section">
                <div class="section-header">
                    <h2><i class="bi bi-pie-chart"></i> Proyectos por Estado</h2>
                </div>
                <?php foreach ($projectsByStatus as $status => $count): 
                    $statusInfo = $PROJECT_STATUSES[$status] ?? $PROJECT_STATUSES['desarrollo'];
                ?>
                <div class="chart-item">
                    <span class="chart-label">
                        <span class="badge badge-<?php echo $statusInfo['badge']; ?>">
                            <?php echo $statusInfo['label']; ?>
                        </span>
                    </span>
                    <span class="chart-value"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2><i class="bi bi-bar-chart"></i> Top Categorías</h2>
                </div>
                <?php foreach ($projectsByCategory as $cat): 
                    $categoryInfo = $MARKETPLACE_CATEGORIES[$cat['category']] ?? $MARKETPLACE_CATEGORIES['otro'];
                ?>
                <div class="chart-item">
                    <span class="chart-label">
                        <i class="bi <?php echo $categoryInfo['icon']; ?>" style="color: <?php echo $categoryInfo['color']; ?>;"></i>
                        <?php echo $categoryInfo['name']; ?>
                    </span>
                    <span class="chart-value"><?php echo $cat['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Proyectos Recientes -->
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-clock-history"></i> Proyectos Recientes</h2>
                <a href="/marketplace/admin/projects.php?action=new" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo Proyecto
                </a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Destacado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProjects as $project): 
                        $categoryInfo = $MARKETPLACE_CATEGORIES[$project['category']] ?? $MARKETPLACE_CATEGORIES['otro'];
                        $statusInfo = $PROJECT_STATUSES[$project['status']] ?? $PROJECT_STATUSES['desarrollo'];
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($project['project_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($project['name']); ?></td>
                        <td>
                            <i class="bi <?php echo $categoryInfo['icon']; ?>" style="color: <?php echo $categoryInfo['color']; ?>;"></i>
                            <?php echo $categoryInfo['name']; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $statusInfo['badge']; ?>">
                                <?php echo $statusInfo['label']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($project['featured']): ?>
                                <i class="bi bi-star-fill" style="color: #f59e0b;"></i>
                            <?php else: ?>
                                <i class="bi bi-star" style="color: #cbd5e0;"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                        <td>
                            <a href="/marketplace/admin/projects.php?action=edit&code=<?php echo urlencode($project['project_code']); ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Estado de Sincronización -->
        <?php if ($syncStats['total'] > 0): ?>
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-arrow-repeat"></i> Estado de Sincronización</h2>
                <a href="/marketplace/admin/sync.php" class="btn btn-secondary">
                    <i class="bi bi-gear"></i> Gestionar Sync
                </a>
            </div>
            
            <div class="chart-container">
                <div class="chart-item">
                    <span class="chart-label">
                        <span class="badge badge-success">Exitosas</span>
                    </span>
                    <span class="chart-value"><?php echo $syncStats['success']; ?></span>
                </div>
                <div class="chart-item">
                    <span class="chart-label">
                        <span class="badge badge-danger">Fallidas</span>
                    </span>
                    <span class="chart-value"><?php echo $syncStats['failed']; ?></span>
                </div>
                <div class="chart-item">
                    <span class="chart-label">
                        <span class="badge badge-secondary">Sin Sincronizar</span>
                    </span>
                    <span class="chart-value"><?php echo $syncStats['never']; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
