<?php
/**
 * Panel Admin del Marketplace - Gestión de Proyectos
 */

require_once __DIR__ . '/auth-admin.php';
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';

$db = getMarketplaceDB();
$action = $_GET['action'] ?? 'list';
$projectCode = $_GET['code'] ?? null;

// Obtener proyecto si estamos editando
$project = null;
if ($action === 'edit' && $projectCode) {
    $stmt = $db->prepare("SELECT * FROM tbl_marketplace_projects WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header('Location: /marketplace/admin/projects.php?error=not_found');
        exit;
    }
}

// Obtener todos los proyectos para el listado
$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM tbl_marketplace_projects WHERE 1=1";
$params = [];

if ($filterCategory) {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
}

if ($filterStatus) {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

if ($search) {
    $sql .= " AND (name LIKE ? OR project_code LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY featured DESC, featured_order ASC, created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $action === 'edit' ? 'Editar Proyecto' : ($action === 'new' ? 'Nuevo Proyecto' : 'Gestión de Proyectos');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Mizton Marketplace Admin</title>
    
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
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #40916C;
            box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
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
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c53030;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
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
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }
        
        .alert-info {
            background: #bee3f8;
            color: #2c5282;
            border-left: 4px solid #3182ce;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #2d3748;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #40916C;
            box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.1);
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            color: #718096;
            font-size: 12px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="bi bi-folder"></i> Gestión de Proyectos</h1>
            <nav class="admin-nav">
                <a href="/marketplace/admin/">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="/marketplace/admin/projects.php" class="active">
                    <i class="bi bi-folder"></i> Proyectos
                </a>
                <a href="/marketplace/admin/sync.php">
                    <i class="bi bi-arrow-repeat"></i> Sincronización
                </a>
                <a href="/marketplace/">
                    <i class="bi bi-eye"></i> Ver Marketplace
                </a>
            </nav>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill" style="font-size: 20px;"></i>
            <span>
                <?php
                switch ($_GET['success']) {
                    case 'created':
                        echo 'Proyecto creado exitosamente';
                        break;
                    case 'updated':
                        echo 'Proyecto actualizado exitosamente';
                        break;
                    case 'deleted':
                        echo 'Proyecto eliminado exitosamente';
                        break;
                    default:
                        echo 'Operación completada exitosamente';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px;"></i>
            <span>
                <?php
                switch ($_GET['error']) {
                    case 'not_found':
                        echo 'Proyecto no encontrado';
                        break;
                    case 'duplicate':
                        echo 'Ya existe un proyecto con ese código';
                        break;
                    default:
                        echo 'Ocurrió un error al procesar la solicitud';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- LISTADO DE PROYECTOS -->
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-list"></i> Todos los Proyectos (<?php echo count($projects); ?>)</h2>
                <a href="/marketplace/admin/projects.php?action=new" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo Proyecto
                </a>
            </div>

            <!-- Filtros -->
            <form method="GET" class="filters">
                <input type="hidden" name="action" value="list">
                
                <div class="filter-group">
                    <label>Buscar</label>
                    <input type="text" name="search" placeholder="Nombre, código o descripción..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Categoría</label>
                    <select name="category">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($MARKETPLACE_CATEGORIES as $key => $cat): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filterCategory === $key ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Estado</label>
                    <select name="status">
                        <option value="">Todos los estados</option>
                        <?php foreach ($PROJECT_STATUSES as $key => $status): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filterStatus === $key ? 'selected' : ''; ?>>
                            <?php echo $status['label']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>

            <!-- Tabla de proyectos -->
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Destacado</th>
                        <th>Método Sync</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #a0aec0;">
                            <i class="bi bi-inbox" style="font-size: 48px; display: block; margin-bottom: 16px;"></i>
                            No se encontraron proyectos
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $proj): 
                            $categoryInfo = $MARKETPLACE_CATEGORIES[$proj['category']] ?? $MARKETPLACE_CATEGORIES['otro'];
                            $statusInfo = $PROJECT_STATUSES[$proj['status']] ?? $PROJECT_STATUSES['desarrollo'];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($proj['project_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($proj['name']); ?></td>
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
                                <?php if ($proj['featured']): ?>
                                    <i class="bi bi-star-fill" style="color: #f59e0b;"></i>
                                    <small style="color: #f59e0b;">#<?php echo $proj['featured_order']; ?></small>
                                <?php else: ?>
                                    <i class="bi bi-star" style="color: #cbd5e0;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?php echo strtoupper($proj['update_method']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($proj['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="/marketplace/project.php?code=<?php echo urlencode($proj['project_code']); ?>" 
                                       class="btn btn-secondary btn-sm" target="_blank" title="Ver en marketplace">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/marketplace/admin/projects.php?action=edit&code=<?php echo urlencode($proj['project_code']); ?>" 
                                       class="btn btn-primary btn-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="deleteProject('<?php echo htmlspecialchars($proj['project_code']); ?>')" 
                                            class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($action === 'edit' || $action === 'new'): ?>
        <!-- FORMULARIO DE EDICIÓN/CREACIÓN -->
        <div class="section">
            <div class="section-header">
                <h2>
                    <i class="bi bi-<?php echo $action === 'new' ? 'plus-circle' : 'pencil'; ?>"></i>
                    <?php echo $action === 'new' ? 'Nuevo Proyecto' : 'Editar Proyecto'; ?>
                </h2>
                <a href="/marketplace/admin/projects.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <form id="projectForm" method="POST" action="/marketplace/admin/api/save-project.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($project): ?>
                <input type="hidden" name="original_code" value="<?php echo htmlspecialchars($project['project_code']); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <!-- Información Básica -->
                    <div class="form-group">
                        <label>Código del Proyecto *</label>
                        <input type="text" name="project_code" required 
                               pattern="[A-Z0-9_-]+" 
                               title="Solo letras mayúsculas, números, guiones y guiones bajos"
                               value="<?php echo htmlspecialchars($project['project_code'] ?? ''); ?>"
                               <?php echo $action === 'edit' ? 'readonly' : ''; ?>>
                        <small>Identificador único (ej: MIZTON, LIBRO1). Solo mayúsculas, números y guiones.</small>
                    </div>

                    <div class="form-group">
                        <label>Nombre del Proyecto *</label>
                        <input type="text" name="name" required 
                               value="<?php echo htmlspecialchars($project['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" required 
                               pattern="[a-z0-9-]+" 
                               title="Solo letras minúsculas, números y guiones"
                               value="<?php echo htmlspecialchars($project['slug'] ?? ''); ?>">
                        <small>URL amigable (ej: mizton-platform). Solo minúsculas y guiones.</small>
                    </div>

                    <div class="form-group">
                        <label>Categoría *</label>
                        <select name="category" required>
                            <option value="">Seleccionar categoría...</option>
                            <?php foreach ($MARKETPLACE_CATEGORIES as $key => $cat): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($project['category'] ?? '') === $key ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="status" required>
                            <?php foreach ($PROJECT_STATUSES as $key => $status): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($project['status'] ?? 'desarrollo') === $key ? 'selected' : ''; ?>>
                                <?php echo $status['label']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>URL del Sitio Web</label>
                        <input type="url" name="website_url" 
                               value="<?php echo htmlspecialchars($project['website_url'] ?? ''); ?>"
                               placeholder="https://ejemplo.com">
                    </div>

                    <!-- Descripción -->
                    <div class="form-group full-width">
                        <label>Descripción Corta *</label>
                        <textarea name="short_description" required maxlength="500"><?php echo htmlspecialchars($project['short_description'] ?? ''); ?></textarea>
                        <small>Máximo 500 caracteres. Se muestra en las tarjetas del marketplace.</small>
                    </div>

                    <div class="form-group full-width">
                        <label>Descripción Completa</label>
                        <textarea name="description" rows="8"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                        <small>Descripción detallada del proyecto. Soporta Markdown.</small>
                    </div>

                    <!-- Imágenes -->
                    <div class="form-group">
                        <label>URL de Imagen Principal</label>
                        <input type="text" name="main_image_url" 
                               value="<?php echo htmlspecialchars($project['main_image_url'] ?? ''); ?>"
                               placeholder="/marketplace/assets/img/projects/proyecto.jpg">
                        <small>Ruta relativa o URL completa de la imagen.</small>
                    </div>

                    <div class="form-group">
                        <label>URL del Logo</label>
                        <input type="text" name="logo_url" 
                               value="<?php echo htmlspecialchars($project['logo_url'] ?? ''); ?>"
                               placeholder="/marketplace/assets/img/logos/proyecto.png">
                    </div>

                    <!-- Datos Financieros -->
                    <div class="form-group">
                        <label>Símbolo del Token</label>
                        <input type="text" name="token_symbol" 
                               value="<?php echo htmlspecialchars($project['token_symbol'] ?? ''); ?>"
                               placeholder="MZT">
                    </div>

                    <div class="form-group">
                        <label>Precio del Token (USD)</label>
                        <input type="number" name="token_price_usd" step="0.000001" min="0"
                               value="<?php echo htmlspecialchars($project['token_price_usd'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Meta de Financiamiento (USD)</label>
                        <input type="number" name="funding_goal" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($project['funding_goal'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Financiamiento Actual (USD)</label>
                        <input type="number" name="funding_raised" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($project['funding_raised'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Porcentaje de Financiamiento</label>
                        <input type="number" name="funding_percentage" step="0.01" min="0" max="100"
                               value="<?php echo htmlspecialchars($project['funding_percentage'] ?? ''); ?>">
                        <small>Se calcula automáticamente si no se especifica.</small>
                    </div>

                    <div class="form-group">
                        <label>Número de Inversores</label>
                        <input type="number" name="holders_count" min="0"
                               value="<?php echo htmlspecialchars($project['holders_count'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>APY / ROI (%)</label>
                        <input type="number" name="apy_percentage" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($project['apy_percentage'] ?? ''); ?>">
                    </div>

                    <!-- Blockchain -->
                    <div class="form-group">
                        <label>Red Blockchain</label>
                        <select name="blockchain_network">
                            <option value="">Seleccionar red...</option>
                            <?php foreach ($BLOCKCHAIN_NETWORKS as $key => $network): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($project['blockchain_network'] ?? '') === $key ? 'selected' : ''; ?>>
                                <?php echo $network['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Dirección del Contrato</label>
                        <input type="text" name="contract_address" 
                               value="<?php echo htmlspecialchars($project['contract_address'] ?? ''); ?>"
                               placeholder="0x...">
                    </div>

                    <!-- Sincronización -->
                    <div class="form-group">
                        <label>Método de Actualización</label>
                        <select name="update_method">
                            <?php foreach ($UPDATE_METHODS as $key => $method): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($project['update_method'] ?? 'manual') === $key ? 'selected' : ''; ?>>
                                <?php echo $method['label']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Frecuencia de Actualización (minutos)</label>
                        <input type="number" name="update_frequency" min="1"
                               value="<?php echo htmlspecialchars($project['update_frequency'] ?? ''); ?>">
                        <small>Solo para métodos automáticos (API, Webhook, Blockchain).</small>
                    </div>

                    <!-- Opciones de Visualización -->
                    <div class="form-group full-width">
                        <div class="checkbox-group">
                            <input type="checkbox" name="featured" id="featured" value="1"
                                   <?php echo ($project['featured'] ?? false) ? 'checked' : ''; ?>>
                            <label for="featured" style="margin: 0;">Proyecto Destacado</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Orden de Destacado</label>
                        <input type="number" name="featured_order" min="0"
                               value="<?php echo htmlspecialchars($project['featured_order'] ?? 0); ?>">
                        <small>Menor número = mayor prioridad.</small>
                    </div>

                    <div class="form-group">
                        <label>Orden de Visualización</label>
                        <input type="number" name="display_order" min="0"
                               value="<?php echo htmlspecialchars($project['display_order'] ?? 0); ?>">
                    </div>

                    <div class="form-group full-width">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   <?php echo ($project['is_active'] ?? true) ? 'checked' : ''; ?>>
                            <label for="is_active" style="margin: 0;">Visible en Marketplace</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/marketplace/admin/projects.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> <?php echo $action === 'new' ? 'Crear Proyecto' : 'Guardar Cambios'; ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($action === 'edit'): ?>
        <!-- Gestión de Milestones (Roadmap) -->
        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-flag"></i> Roadmap del Proyecto (Milestones)</h2>
                <button type="button" onclick="addMilestone()" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Agregar Milestone
                </button>
            </div>
            
            <?php
            // Obtener milestones existentes
            $stmt = $db->prepare("
                SELECT * FROM tbl_marketplace_milestones 
                WHERE project_id = ? 
                ORDER BY target_date ASC, display_order ASC
            ");
            $stmt->execute([$project['id']]);
            $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div id="milestonesContainer">
                <?php if (empty($milestones)): ?>
                <p style="color: #718096; text-align: center; padding: 20px;">
                    No hay milestones configurados. Agrega el primer milestone del roadmap.
                </p>
                <?php else: ?>
                <div class="milestones-list">
                    <?php foreach ($milestones as $milestone): ?>
                    <div class="milestone-item" data-id="<?php echo $milestone['id']; ?>">
                        <div class="milestone-header">
                            <div class="milestone-status">
                                <span class="status-badge status-<?php echo $milestone['status']; ?>">
                                    <?php echo strtoupper($milestone['status']); ?>
                                </span>
                                <?php if ($milestone['target_date']): ?>
                                <span class="milestone-date">
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($milestone['target_date'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="milestone-actions">
                                <button type="button" onclick="editMilestone(<?php echo $milestone['id']; ?>)" class="btn-icon" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" onclick="deleteMilestone(<?php echo $milestone['id']; ?>)" class="btn-icon" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <h4><?php echo htmlspecialchars($milestone['title']); ?></h4>
                        <?php if ($milestone['description']): ?>
                        <p><?php echo nl2br(htmlspecialchars($milestone['description'])); ?></p>
                        <?php endif; ?>
                        <?php if ($milestone['completion_percentage'] > 0): ?>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $milestone['completion_percentage']; ?>%"></div>
                            <span class="progress-text"><?php echo $milestone['completion_percentage']; ?>%</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2><i class="bi bi-file-earmark-text"></i> Documentos del Proyecto</h2>
                <button onclick="window.location.href='/marketplace/admin/documents.php?project=<?php echo urlencode($projectCode); ?>'" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Gestionar Documentos
                </button>
            </div>
            <p style="color: #718096;">Gestiona whitepapers, pitch decks y otros documentos desde la sección de documentos.</p>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        function deleteProject(projectCode) {
            if (!confirm('¿Estás seguro de que deseas eliminar este proyecto?\n\nEsta acción eliminará también todos sus milestones, documentos y estadísticas asociadas.')) {
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('project_code', projectCode);

            fetch('/marketplace/admin/api/delete-project.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/marketplace/admin/projects.php?success=deleted';
                } else {
                    alert('Error al eliminar el proyecto: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el proyecto');
            });
        }

        // Auto-generar slug desde el nombre
        document.querySelector('input[name="name"]')?.addEventListener('input', function(e) {
            const slugInput = document.querySelector('input[name="slug"]');
            if (slugInput && !slugInput.value) {
                const slug = e.target.value
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });

        // Calcular porcentaje de financiamiento automáticamente
        const fundingGoal = document.querySelector('input[name="funding_goal"]');
        const fundingRaised = document.querySelector('input[name="funding_raised"]');
        const fundingPercentage = document.querySelector('input[name="funding_percentage"]');

        function calculatePercentage() {
            if (fundingGoal && fundingRaised && fundingPercentage) {
                const goal = parseFloat(fundingGoal.value) || 0;
                const raised = parseFloat(fundingRaised.value) || 0;
                if (goal > 0) {
                    fundingPercentage.value = ((raised / goal) * 100).toFixed(2);
                }
            }
        }

        fundingGoal?.addEventListener('input', calculatePercentage);
        fundingRaised?.addEventListener('input', calculatePercentage);

        // ========================================
        // Funciones para Gestión de Milestones
        // ========================================
        
        function addMilestone() {
            const title = prompt('Título del milestone:');
            if (!title) return;
            
            const description = prompt('Descripción (opcional):');
            const targetDate = prompt('Fecha objetivo (YYYY-MM-DD):');
            const status = prompt('Estado (pending/in_progress/completed/cancelled):', 'pending');
            
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('action', 'add');
            formData.append('project_id', '<?php echo $project['id']; ?>');
            formData.append('title', title);
            formData.append('description', description || '');
            formData.append('target_date', targetDate || '');
            formData.append('status', status);
            formData.append('completion_percentage', 0);
            
            fetch('/marketplace/admin/api/manage-milestones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al agregar milestone');
            });
        }
        
        function editMilestone(id) {
            // Obtener datos actuales del milestone
            fetch('/marketplace/admin/api/manage-milestones.php?action=get&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al cargar milestone');
                    return;
                }
                
                const milestone = data.milestone;
                const title = prompt('Título:', milestone.title);
                if (title === null) return;
                
                const description = prompt('Descripción:', milestone.description || '');
                const targetDate = prompt('Fecha objetivo (YYYY-MM-DD):', milestone.target_date || '');
                const status = prompt('Estado (pending/in_progress/completed/cancelled):', milestone.status);
                const completion = prompt('Porcentaje de completado (0-100):', milestone.completion_percentage || 0);
                
                const formData = new FormData();
                formData.append('csrf_token', '<?php echo $csrf_token; ?>');
                formData.append('action', 'update');
                formData.append('id', id);
                formData.append('title', title);
                formData.append('description', description || '');
                formData.append('target_date', targetDate || '');
                formData.append('status', status);
                formData.append('completion_percentage', completion);
                
                fetch('/marketplace/admin/api/manage-milestones.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar milestone');
                });
            });
        }
        
        function deleteMilestone(id) {
            if (!confirm('¿Eliminar este milestone?')) return;
            
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/marketplace/admin/api/manage-milestones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar milestone');
            });
        }
    </script>
    
    <style>
        .milestones-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .milestone-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            transition: box-shadow 0.2s;
        }
        
        .milestone-item:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .milestone-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .milestone-status {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .milestone-date {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .milestone-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .btn-icon:hover {
            background: #f1f5f9;
            color: #334155;
        }
        
        .milestone-item h4 {
            margin: 0 0 8px 0;
            color: #1e293b;
            font-size: 1.1rem;
        }
        
        .milestone-item p {
            margin: 0 0 12px 0;
            color: #64748b;
            line-height: 1.6;
        }
        
        .progress-bar {
            position: relative;
            height: 24px;
            background: #f1f5f9;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            transition: width 0.3s;
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e293b;
        }
    </style>
</body>
</html>
