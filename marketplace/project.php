<?php
/**
 * Marketplace Mizton - Vista Detalle de Proyecto
 */

require_once __DIR__ . '/config/marketplace-config.php';
require_once __DIR__ . '/includes/marketplace-functions.php';

// Obtener slug del proyecto
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /marketplace/');
    exit;
}

// Obtener proyecto
$project = getProjectBySlug($slug);

if (!$project) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>Proyecto no encontrado</h1>";
    exit;
}

// Registrar vista
recordProjectView($project['id']);

// Obtener datos adicionales
$documents = getProjectDocuments($project['id']);
$milestones = getProjectMilestones($project['id']);
$cachedData = getProjectCachedData($project['id']);

// Información de categoría y estado
$categoryInfo = $MARKETPLACE_CATEGORIES[$project['category']] ?? $MARKETPLACE_CATEGORIES['otro'];
$statusInfo = $PROJECT_STATUSES[$project['status']] ?? $PROJECT_STATUSES['desarrollo'];
$networkInfo = $BLOCKCHAIN_NETWORKS[$project['blockchain_network']] ?? null;

$isLoggedIn = isset($_SESSION['idUser']);
$pageTitle = $project['name'] . ' - Marketplace';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Mizton</title>
    
    <meta name="description" content="<?php echo htmlspecialchars($project['short_description'] ?: substr($project['description'], 0, 160)); ?>">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/marketplace/assets/css/marketplace.css">
    <link rel="icon" href="/panel/dist/img/favicon.ico">
</head>
<body>

    <!-- Navegación -->
    <header class="marketplace-nav">
        <div class="marketplace-container">
            <div class="nav-wrapper">
                <div class="nav-brand">
                    <a href="/marketplace">
                        <img src="/assets/img/logo.png" alt="Mizton" class="nav-logo" onerror="this.style.display='none'">
                        <span class="brand-text">Mizton</span>
                        <span class="nav-label">Marketplace</span>
                    </a>
                </div>
                <nav class="nav-links">
                    <a href="/marketplace"><i class="bi bi-arrow-left"></i> Marketplace</a>
                    <a href="/">Inicio</a>
                    <?php if ($isLoggedIn): ?>
                    <a href="https://panel.mizton.cat/">Mi Panel</a>
                    <a href="https://panel.mizton.cat/profile.php" class="nav-user">
                        <i class="bi bi-person-circle"></i> Mi Cuenta
                    </a>
                    <?php else: ?>
                    <a href="https://panel.mizton.cat/login.php">Iniciar Sesión</a>
                    <a href="https://panel.mizton.cat/register.php" class="cta-link">Registrarse</a>
                    <?php endif; ?>
                </nav>
                <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <div class="project-detail-container">

        <!-- Header del Proyecto -->
        <div class="project-detail-header">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                <span class="project-code" style="font-size: 1rem; padding: 8px 16px;">
                    <?php echo htmlspecialchars($project['project_code']); ?>
                </span>
                <span class="project-status status-<?php echo $project['status']; ?>">
                    <i class="bi <?php echo $statusInfo['icon']; ?>"></i>
                    <?php echo $statusInfo['label']; ?>
                </span>
                <?php if ($project['featured']): ?>
                <span class="badge" style="background: var(--mizton-orange); color: white;">
                    <i class="bi bi-star-fill"></i> Destacado
                </span>
                <?php endif; ?>
            </div>

            <h1 class="project-detail-title"><?php echo htmlspecialchars($project['name']); ?></h1>

            <div class="project-meta">
                <div class="project-category" style="background: <?php echo $categoryInfo['color']; ?>20; color: <?php echo $categoryInfo['color']; ?>;">
                    <i class="bi <?php echo $categoryInfo['icon']; ?>"></i>
                    <span><?php echo $categoryInfo['name']; ?></span>
                </div>
                <?php if ($networkInfo): ?>
                <div style="display: flex; align-items: center; gap: 8px; color: #666;">
                    <i class="bi bi-diagram-3"></i>
                    <span><?php echo $networkInfo['name']; ?></span>
                </div>
                <?php endif; ?>
                <?php if ($project['holders_count']): ?>
                <div style="display: flex; align-items: center; gap: 8px; color: #666;">
                    <i class="bi bi-people"></i>
                    <span><?php echo number_format($project['holders_count']); ?> inversionistas</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="project-detail-content">
            
            <!-- Columna Principal -->
            <div class="main-content">
                
                <!-- Imagen Principal -->
                <?php if ($project['main_image_url']): ?>
                <img src="<?php echo htmlspecialchars($project['main_image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($project['name']); ?>"
                     style="width: 100%; border-radius: 12px; margin-bottom: 30px;"
                     onerror="this.style.display='none'">
                <?php endif; ?>

                <!-- Tabs de Contenido -->
                <div class="tabs-container">
                    <div class="tabs-nav">
                        <button class="tab-button active" data-tab="description">
                            <i class="bi bi-file-text"></i> Descripción
                        </button>
                        <?php if (!empty($milestones)): ?>
                        <button class="tab-button" data-tab="milestones">
                            <i class="bi bi-flag"></i> Roadmap
                        </button>
                        <?php endif; ?>
                        <?php if ($project['contract_address']): ?>
                        <button class="tab-button" data-tab="blockchain">
                            <i class="bi bi-diagram-3"></i> Blockchain
                        </button>
                        <?php endif; ?>
                        <?php if (!empty($documents)): ?>
                        <button class="tab-button" data-tab="documents">
                            <i class="bi bi-file-earmark-pdf"></i> Documentos
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Descripción -->
                    <div id="description" class="tab-content active">
                        <div style="line-height: 1.8; color: #333;">
                            <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                        </div>
                    </div>

                    <!-- Tab: Milestones -->
                    <?php if (!empty($milestones)): ?>
                    <div id="milestones" class="tab-content">
                        <div class="milestones-list">
                            <?php foreach ($milestones as $milestone): ?>
                            <div class="milestone-item <?php echo $milestone['status']; ?>">
                                <div class="milestone-icon">
                                    <?php if ($milestone['status'] === 'completed'): ?>
                                    <i class="bi bi-check-circle-fill" style="color: var(--mizton-green);"></i>
                                    <?php elseif ($milestone['status'] === 'in_progress'): ?>
                                    <i class="bi bi-arrow-repeat" style="color: var(--mizton-blue);"></i>
                                    <?php else: ?>
                                    <i class="bi bi-circle" style="color: #ccc;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="milestone-content">
                                    <div class="milestone-name"><?php echo htmlspecialchars($milestone['milestone_name']); ?></div>
                                    <?php if ($milestone['description']): ?>
                                    <div class="milestone-description"><?php echo htmlspecialchars($milestone['description']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($milestone['progress_percentage'] > 0): ?>
                                    <div style="margin-top: 10px;">
                                        <div class="progress-bar-container" style="height: 8px;">
                                            <div class="progress-bar-fill" style="width: <?php echo $milestone['progress_percentage']; ?>%"></div>
                                        </div>
                                        <small style="color: #666;"><?php echo formatPercentage($milestone['progress_percentage']); ?> completado</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tab: Blockchain -->
                    <?php if ($project['contract_address']): ?>
                    <div id="blockchain" class="tab-content">
                        <div class="blockchain-info">
                            <div class="metric-row">
                                <span class="metric-label">Red Blockchain</span>
                                <span class="metric-value"><?php echo $networkInfo['name'] ?? $project['blockchain_network']; ?></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Símbolo del Token</span>
                                <span class="metric-value"><?php echo htmlspecialchars($project['token_symbol']); ?></span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Contrato</span>
                                <span class="metric-value">
                                    <div class="contract-address"><?php echo htmlspecialchars($project['contract_address']); ?></div>
                                </span>
                            </div>
                            <?php if ($networkInfo && $networkInfo['explorer']): ?>
                            <div style="margin-top: 20px;">
                                <a href="<?php echo $networkInfo['explorer']; ?>/address/<?php echo $project['contract_address']; ?>" 
                                   target="_blank"
                                   class="btn-primary-action"
                                   style="background: rgba(255,255,255,0.2);">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                    Ver en Block Explorer
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tab: Documentos -->
                    <?php if (!empty($documents)): ?>
                    <div id="documents" class="tab-content">
                        <div class="documents-list">
                            <?php foreach ($documents as $doc): ?>
                            <div class="document-item">
                                <div class="document-icon">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                                <div class="document-info">
                                    <div class="document-name"><?php echo htmlspecialchars($doc['document_name']); ?></div>
                                    <div class="document-meta">
                                        <?php echo ucfirst($doc['document_type']); ?>
                                        <?php if ($doc['file_size']): ?>
                                        • <?php echo formatLargeNumber($doc['file_size']); ?> bytes
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?php echo htmlspecialchars($doc['document_url']); ?>" 
                                   target="_blank" 
                                   class="btn-download">
                                    <i class="bi bi-download"></i> Descargar
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="sidebar-content">
                
                <!-- Métricas Principales -->
                <div class="info-card">
                    <h3><i class="bi bi-graph-up"></i> Métricas del Proyecto</h3>
                    
                    <?php if ($project['token_price_usd']): ?>
                    <div class="metric-row">
                        <span class="metric-label">Precio del Token</span>
                        <span class="metric-value" style="color: var(--mizton-green);">
                            <?php echo formatCurrency($project['token_price_usd']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ($project['market_cap']): ?>
                    <div class="metric-row">
                        <span class="metric-label">Market Cap</span>
                        <span class="metric-value"><?php echo formatCurrency($project['market_cap']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($project['apy_percentage']): ?>
                    <div class="metric-row">
                        <span class="metric-label">APY/ROI Proyectado</span>
                        <span class="metric-value" style="color: var(--mizton-blue);">
                            <?php echo formatPercentage($project['apy_percentage']); ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <?php if ($project['total_supply']): ?>
                    <div class="metric-row">
                        <span class="metric-label">Supply Total</span>
                        <span class="metric-value"><?php echo formatLargeNumber($project['total_supply']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($project['circulating_supply']): ?>
                    <div class="metric-row">
                        <span class="metric-label">Supply Circulante</span>
                        <span class="metric-value"><?php echo formatLargeNumber($project['circulating_supply']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Progreso de Financiamiento -->
                <?php if ($project['funding_goal'] && $project['funding_percentage'] > 0): ?>
                <div class="info-card">
                    <h3><i class="bi bi-cash-stack"></i> Financiamiento</h3>
                    
                    <div class="funding-progress">
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?php echo min($project['funding_percentage'], 100); ?>%"></div>
                        </div>
                        <div class="progress-info">
                            <span><?php echo formatPercentage($project['funding_percentage']); ?></span>
                        </div>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Recaudado</span>
                        <span class="metric-value"><?php echo formatCurrency($project['funding_raised']); ?></span>
                    </div>

                    <div class="metric-row">
                        <span class="metric-label">Meta</span>
                        <span class="metric-value"><?php echo formatCurrency($project['funding_goal']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botón de Acción Principal -->
                <div class="info-card">
                    <?php if ($project['website_url']): ?>
                    <a href="<?php echo htmlspecialchars($project['website_url']); ?>" 
                       target="_blank"
                       class="btn-primary-action"
                       onclick="recordClickThrough(<?php echo $project['id']; ?>)">
                        <i class="bi bi-box-arrow-up-right"></i>
                        Ir al Proyecto
                    </a>
                    <?php endif; ?>

                    <?php if ($project['dashboard_url']): ?>
                    <a href="<?php echo htmlspecialchars($project['dashboard_url']); ?>" 
                       target="_blank"
                       class="btn-primary-action"
                       style="background: var(--mizton-blue); margin-top: 10px;">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard del Proyecto
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Enlaces Adicionales -->
                <?php if ($project['twitter_url'] || $project['telegram_url'] || $project['discord_url']): ?>
                <div class="info-card">
                    <h3><i class="bi bi-share"></i> Redes Sociales</h3>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <?php if ($project['twitter_url']): ?>
                        <a href="<?php echo htmlspecialchars($project['twitter_url']); ?>" target="_blank" style="font-size: 1.5rem; color: #1DA1F2;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($project['telegram_url']): ?>
                        <a href="<?php echo htmlspecialchars($project['telegram_url']); ?>" target="_blank" style="font-size: 1.5rem; color: #0088cc;">
                            <i class="bi bi-telegram"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($project['discord_url']): ?>
                        <a href="<?php echo htmlspecialchars($project['discord_url']); ?>" target="_blank" style="font-size: 1.5rem; color: #5865F2;">
                            <i class="bi bi-discord"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- Footer -->
    <footer class="marketplace-footer">
        <div class="marketplace-container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="/assets/img/logo.png" alt="Mizton" class="footer-logo" onerror="this.style.display='none'">
                    <span class="footer-brand-text">Mizton Marketplace</span>
                </div>
                <div class="footer-links">
                    <a href="/">Inicio</a>
                    <a href="/marketplace">Marketplace</a>
                    <a href="/news">Blog</a>
                    <a href="https://panel.mizton.cat/">Panel</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mizton. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
    function toggleMobileMenu() {
        document.querySelector('.nav-links').classList.toggle('active');
        document.querySelector('.mobile-menu-toggle').classList.toggle('active');
    }
    </script>
    <script src="/marketplace/assets/js/marketplace.js"></script>
    <script>
    function recordClickThrough(projectId) {
        fetch('/marketplace/api/record-analytics.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'click_through', project_id: projectId })
        }).catch(err => console.error('Error:', err));
    }
    </script>

</body>
</html>
