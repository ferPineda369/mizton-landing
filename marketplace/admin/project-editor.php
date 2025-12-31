<?php
/**
 * Editor Completo de Proyectos con Landing Interna
 * Panel Admin del Marketplace - Nueva versión con soporte completo
 */

require_once __DIR__ . '/auth-admin.php';
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../config/project-types-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';
require_once __DIR__ . '/../includes/project-metadata-functions.php';

$db = getMarketplaceDB();
$projectCode = $_GET['code'] ?? null;
$tab = $_GET['tab'] ?? 'basic';

// Obtener proyecto si estamos editando
$project = null;
$isEdit = false;
if ($projectCode) {
    $project = getCompleteProject($projectCode);
    if (!$project) {
        header('Location: /marketplace/admin/projects.php?error=not_found');
        exit;
    }
    $isEdit = true;
}

// Obtener tipos de proyecto disponibles
$projectTypes = getAllProjectTypes();

// Preparar metadata como array simple si existe
$metadata = [];
if ($project && !empty($project['metadata'])) {
    foreach ($project['metadata'] as $key => $data) {
        $metadata[$key] = $data['value'];
    }
}

$pageTitle = $isEdit ? 'Editar Proyecto: ' . $project['name'] : 'Nuevo Proyecto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Mizton Marketplace Admin</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
    
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
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            overflow-x: auto;
        }
        
        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            color: #718096;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .tab:hover {
            color: #40916C;
        }
        
        .tab.active {
            color: #40916C;
            border-bottom-color: #40916C;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .card-header h2 {
            font-size: 20px;
            color: #2d3748;
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
        
        .form-group label .required {
            color: #e53e3e;
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
            margin-top: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        .alert-warning {
            background: #feebc8;
            color: #7c2d12;
            border-left: 4px solid #dd6b20;
        }
        
        .metadata-fields {
            display: none;
        }
        
        .metadata-fields.active {
            display: block;
        }
        
        .section-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 2px solid #e2e8f0;
            cursor: move;
        }
        
        .section-item:hover {
            border-color: #40916C;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-actions {
            display: flex;
            gap: 8px;
        }
        
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .media-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }
        
        .media-item img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
        }
        
        .media-item .media-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 5px;
        }
        
        .media-item .media-info {
            padding: 10px;
            background: white;
        }
        
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: #40916C;
            background: #f7fafc;
        }
        
        .upload-area i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 15px;
        }
        
        .list-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .list-item-content {
            flex: 1;
        }
        
        .list-item-actions {
            display: flex;
            gap: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
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
        
        .badge-info {
            background: #bee3f8;
            color: #2c5282;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <h1>
                <i class="bi bi-pencil-square"></i>
                <?php echo $isEdit ? 'Editar Proyecto' : 'Nuevo Proyecto'; ?>
            </h1>
            <div class="header-actions">
                <?php if ($isEdit): ?>
                    <a href="/marketplace/project-landing.php?code=<?php echo $project['project_code']; ?>" 
                       target="_blank" class="btn btn-secondary">
                        <i class="bi bi-eye"></i> Ver Landing
                    </a>
                <?php endif; ?>
                <a href="/marketplace/admin/projects.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        
        <?php if ($isEdit && $project['has_internal_landing']): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Proyecto con Landing Interna</strong><br>
                    Este proyecto usa una landing page interna. Puedes gestionar todas las secciones, metadata y contenido desde este panel.
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="basic">
                <i class="bi bi-info-circle"></i> Información Básica
            </button>
            <?php if ($isEdit): ?>
                <button class="tab" data-tab="metadata">
                    <i class="bi bi-tags"></i> Metadata Específica
                </button>
                <button class="tab" data-tab="sections">
                    <i class="bi bi-layout-text-window"></i> Secciones
                </button>
                <button class="tab" data-tab="media">
                    <i class="bi bi-images"></i> Multimedia
                </button>
                <button class="tab" data-tab="team">
                    <i class="bi bi-people"></i> Equipo
                </button>
                <button class="tab" data-tab="faq">
                    <i class="bi bi-question-circle"></i> FAQ
                </button>
                <button class="tab" data-tab="milestones">
                    <i class="bi bi-flag"></i> Milestones
                </button>
            <?php endif; ?>
        </div>

        <!-- Tab: Información Básica -->
        <div class="tab-content active" id="tab-basic">
            <form id="form-basic" method="POST" action="/marketplace/admin/api/save-project.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Información General</h2>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Código del Proyecto <span class="required">*</span></label>
                            <input type="text" name="project_code" 
                                   value="<?php echo htmlspecialchars($project['project_code'] ?? ''); ?>"
                                   <?php echo $isEdit ? 'readonly' : ''; ?>
                                   required>
                            <small>Código único (ej: LIBRO1, MUSIC01)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Nombre del Proyecto <span class="required">*</span></label>
                            <input type="text" name="name" 
                                   value="<?php echo htmlspecialchars($project['name'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Categoría <span class="required">*</span></label>
                            <select name="category" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach (MARKETPLACE_CATEGORIES as $key => $cat): ?>
                                    <option value="<?php echo $key; ?>" 
                                            <?php echo ($project['category'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Estado <span class="required">*</span></label>
                            <select name="status" required>
                                <?php foreach (MARKETPLACE_STATUSES as $key => $status): ?>
                                    <option value="<?php echo $key; ?>"
                                            <?php echo ($project['status'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo $status['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Descripción Corta <span class="required">*</span></label>
                            <textarea name="short_description" required><?php echo htmlspecialchars($project['short_description'] ?? ''); ?></textarea>
                            <small>Resumen breve para el listado (máx. 200 caracteres)</small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Descripción Completa</label>
                            <textarea name="description" rows="4"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Configuración de Landing Interna</h2>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="has_internal_landing" id="has_internal_landing" value="1"
                                   <?php echo ($project['has_internal_landing'] ?? false) ? 'checked' : ''; ?>>
                            <label for="has_internal_landing" style="margin: 0;">
                                Usar Landing Page Interna
                            </label>
                        </div>
                        <small>Activa esto si el proyecto no tiene sitio web propio</small>
                    </div>
                    
                    <div id="landing-config" style="<?php echo ($project['has_internal_landing'] ?? false) ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label>Tipo de Proyecto <span class="required">*</span></label>
                            <select name="project_type" id="project_type">
                                <?php foreach ($projectTypes as $typeKey => $typeConfig): ?>
                                    <option value="<?php echo $typeKey; ?>"
                                            data-icon="<?php echo $typeConfig['icon']; ?>"
                                            data-color="<?php echo $typeConfig['color']; ?>"
                                            <?php echo ($project['project_type'] ?? 'general') === $typeKey ? 'selected' : ''; ?>>
                                        <?php echo $typeConfig['name']; ?> - <?php echo $typeConfig['description']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Selecciona el tipo para cargar campos específicos</small>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Descripción Larga (HTML permitido)</label>
                            <textarea name="long_description" rows="8"><?php echo htmlspecialchars($project['long_description'] ?? ''); ?></textarea>
                            <small>Contenido detallado para la sección "Sobre el Proyecto". Puedes usar HTML.</small>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Información Financiera</h2>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Símbolo del Token <span class="required">*</span></label>
                            <input type="text" name="token_symbol" 
                                   value="<?php echo htmlspecialchars($project['token_symbol'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio por Token (USD) <span class="required">*</span></label>
                            <input type="number" step="0.01" name="token_price_usd" 
                                   value="<?php echo $project['token_price_usd'] ?? ''; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta de Financiamiento (USD)</label>
                            <input type="number" step="0.01" name="funding_goal" 
                                   value="<?php echo $project['funding_goal'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Financiamiento Actual (USD)</label>
                            <input type="number" step="0.01" name="funding_raised" 
                                   value="<?php echo $project['funding_raised'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Red Blockchain</label>
                            <select name="blockchain_network">
                                <option value="">Seleccionar...</option>
                                <?php foreach (BLOCKCHAIN_NETWORKS as $key => $network): ?>
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
                                   value="<?php echo htmlspecialchars($project['contract_address'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Configuración Adicional</h2>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>URL de la Imagen Principal</label>
                            <input type="url" name="main_image_url" 
                                   value="<?php echo htmlspecialchars($project['main_image_url'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>URL del Sitio Web</label>
                            <input type="url" name="website_url" 
                                   value="<?php echo htmlspecialchars($project['website_url'] ?? ''); ?>">
                            <small>Dejar vacío si usa landing interna</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="featured" id="featured" value="1"
                                       <?php echo ($project['featured'] ?? false) ? 'checked' : ''; ?>>
                                <label for="featured" style="margin: 0;">Proyecto Destacado</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       <?php echo ($project['is_active'] ?? true) ? 'checked' : ''; ?>>
                                <label for="is_active" style="margin: 0;">Proyecto Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="/marketplace/admin/projects.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        <?php echo $isEdit ? 'Guardar Cambios' : 'Crear Proyecto'; ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($isEdit): ?>
        <!-- Tab: Metadata Específica -->
        <div class="tab-content" id="tab-metadata">
            <div class="card">
                <div class="card-header">
                    <h2>Campos Específicos del Tipo de Proyecto</h2>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        Los campos disponibles dependen del tipo de proyecto seleccionado.
                        Tipo actual: <strong><?php echo $projectTypes[$project['project_type']]['name']; ?></strong>
                    </div>
                </div>
                
                <form id="form-metadata" method="POST" action="/marketplace/admin/api/save-metadata.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                    
                    <div class="form-grid" id="metadata-fields-container">
                        <?php
                        $typeConfig = $projectTypes[$project['project_type']];
                        foreach ($typeConfig['metadata_fields'] as $fieldKey => $fieldConfig):
                            $value = $metadata[$fieldKey] ?? '';
                        ?>
                            <div class="form-group <?php echo in_array($fieldConfig['type'], ['json', 'textarea']) ? 'full-width' : ''; ?>">
                                <label>
                                    <?php echo $fieldConfig['label']; ?>
                                    <?php if ($fieldConfig['required']): ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                
                                <?php if ($fieldConfig['type'] === 'select'): ?>
                                    <select name="metadata[<?php echo $fieldKey; ?>]" 
                                            <?php echo $fieldConfig['required'] ? 'required' : ''; ?>>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($fieldConfig['options'] as $optKey => $optLabel): ?>
                                            <option value="<?php echo $optKey; ?>"
                                                    <?php echo $value === $optKey ? 'selected' : ''; ?>>
                                                <?php echo $optLabel; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($fieldConfig['type'] === 'textarea' || $fieldConfig['type'] === 'json'): ?>
                                    <textarea name="metadata[<?php echo $fieldKey; ?>]" 
                                              rows="4"
                                              placeholder="<?php echo $fieldConfig['placeholder'] ?? ''; ?>"
                                              <?php echo $fieldConfig['required'] ? 'required' : ''; ?>><?php echo htmlspecialchars($value); ?></textarea>
                                <?php else: ?>
                                    <input type="<?php echo $fieldConfig['type']; ?>" 
                                           name="metadata[<?php echo $fieldKey; ?>]"
                                           value="<?php echo htmlspecialchars($value); ?>"
                                           placeholder="<?php echo $fieldConfig['placeholder'] ?? ''; ?>"
                                           <?php echo isset($fieldConfig['min']) ? 'min="' . $fieldConfig['min'] . '"' : ''; ?>
                                           <?php echo $fieldConfig['required'] ? 'required' : ''; ?>>
                                <?php endif; ?>
                                
                                <?php if (!empty($fieldConfig['help'])): ?>
                                    <small><?php echo $fieldConfig['help']; ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Metadata
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Placeholder para otros tabs -->
        <div class="tab-content" id="tab-sections">
            <div class="card">
                <div class="card-header">
                    <h2>Gestión de Secciones</h2>
                </div>
                <div class="empty-state">
                    <i class="bi bi-layout-text-window"></i>
                    <h3>Gestor de Secciones</h3>
                    <p>Funcionalidad en desarrollo</p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-media">
            <div class="card">
                <div class="card-header">
                    <h2>Galería Multimedia</h2>
                </div>
                <div class="empty-state">
                    <i class="bi bi-images"></i>
                    <h3>Gestor de Multimedia</h3>
                    <p>Funcionalidad en desarrollo</p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-team">
            <div class="card">
                <div class="card-header">
                    <h2>Equipo del Proyecto</h2>
                </div>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h3>Gestor de Equipo</h3>
                    <p>Funcionalidad en desarrollo</p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-faq">
            <div class="card">
                <div class="card-header">
                    <h2>Preguntas Frecuentes</h2>
                </div>
                <div class="empty-state">
                    <i class="bi bi-question-circle"></i>
                    <h3>Gestor de FAQ</h3>
                    <p>Funcionalidad en desarrollo</p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="tab-milestones">
            <div class="card">
                <div class="card-header">
                    <h2>Milestones del Proyecto</h2>
                </div>
                <div class="empty-state">
                    <i class="bi bi-flag"></i>
                    <h3>Gestor de Milestones</h3>
                    <p>Funcionalidad en desarrollo</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab').on('click', function() {
                const tabId = $(this).data('tab');
                
                $('.tab').removeClass('active');
                $(this).addClass('active');
                
                $('.tab-content').removeClass('active');
                $('#tab-' + tabId).addClass('active');
                
                // Update URL
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId);
                window.history.pushState({}, '', url);
            });
            
            // Toggle landing config
            $('#has_internal_landing').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#landing-config').slideDown();
                } else {
                    $('#landing-config').slideUp();
                }
            });
            
            // Form submission
            $('#form-basic').on('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Proyecto guardado exitosamente');
                            if (response.project_code) {
                                window.location.href = '/marketplace/admin/project-editor.php?code=' + response.project_code;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al guardar el proyecto');
                    }
                });
            });
            
            // Metadata form submission
            $('#form-metadata').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            alert('Metadata guardada exitosamente');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al guardar metadata');
                    }
                });
            });
        });
    </script>
</body>
</html>
