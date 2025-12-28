<?php
/**
 * Marketplace Mizton - Vista Principal
 * Listado de proyectos tokenizados
 */

require_once __DIR__ . '/config/marketplace-config.php';
require_once __DIR__ . '/includes/marketplace-functions.php';

// Verificar si el marketplace está activo
if (!isMarketplaceEnabled()) {
    include __DIR__ . '/maintenance.php';
    exit;
}

// Obtener categorías y contadores
$categories = getActiveCategories();
$categoryCounts = countProjectsByCategory();

// Obtener proyectos destacados
$featuredProjects = getFeaturedProjects(6);

// Incluir header del panel si el usuario está logueado
$isLoggedIn = isset($_SESSION['idUser']);
$pageTitle = 'Marketplace - Proyectos Tokenizados';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Mizton</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Explora proyectos tokenizados de activos reales. Invierte en inmobiliario, energía, arte, música y más a través de blockchain.">
    <meta name="keywords" content="tokenización, RWA, blockchain, inversión, activos reales, mizton">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="Proyectos tokenizados de activos reales">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo MARKETPLACE_URL; ?>">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Marketplace CSS -->
    <link rel="stylesheet" href="/marketplace/assets/css/marketplace.css">
    
    <!-- Favicon -->
    <link rel="icon" href="/panel/dist/img/favicon.ico">
</head>
<body>

    <!-- Navegación -->
    <nav style="background: var(--mizton-dark); padding: 15px 0; margin-bottom: 30px;">
        <div class="marketplace-container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 30px;">
                    <a href="/" style="color: white; text-decoration: none; font-size: 1.5rem; font-weight: 700;">
                        <i class="bi bi-grid-3x3-gap"></i> Mizton Marketplace
                    </a>
                    <a href="/" style="color: rgba(255,255,255,0.8); text-decoration: none;">
                        <i class="bi bi-house"></i> Inicio
                    </a>
                    <?php if ($isLoggedIn): ?>
                    <a href="/panel/" style="color: rgba(255,255,255,0.8); text-decoration: none;">
                        <i class="bi bi-speedometer2"></i> Mi Panel
                    </a>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($isLoggedIn): ?>
                    <a href="/panel/profile.php" style="color: white; text-decoration: none;">
                        <i class="bi bi-person-circle"></i> Mi Cuenta
                    </a>
                    <?php else: ?>
                    <a href="/panel/login.php" style="color: white; text-decoration: none; margin-right: 20px;">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </a>
                    <a href="/panel/register.php" style="background: var(--mizton-green); color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">
                        <i class="bi bi-person-plus"></i> Registrarse
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header del Marketplace -->
    <div class="marketplace-header">
        <h1>Proyectos Tokenizados</h1>
        <p>Invierte en activos reales a través de blockchain</p>
    </div>

    <div class="marketplace-container">

        <!-- Filtros y Búsqueda -->
        <div class="marketplace-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="category-filter">
                        <i class="bi bi-tag"></i> Categoría
                    </label>
                    <select id="category-filter">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_key']; ?>">
                            <?php echo $cat['category_name']; ?>
                            <?php if (isset($categoryCounts[$cat['category_key']])): ?>
                            (<?php echo $categoryCounts[$cat['category_key']]; ?>)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status-filter">
                        <i class="bi bi-flag"></i> Estado
                    </label>
                    <select id="status-filter">
                        <option value="">Todos los estados</option>
                        <option value="preventa">Preventa</option>
                        <option value="activo">Activo</option>
                        <option value="financiado">Financiado</option>
                        <option value="desarrollo">En Desarrollo</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort-filter">
                        <i class="bi bi-sort-down"></i> Ordenar por
                    </label>
                    <select id="sort-filter">
                        <option value="default">Destacados primero</option>
                        <option value="funding">% Financiamiento</option>
                        <option value="apy">APY/ROI</option>
                        <option value="price_asc">Precio (menor a mayor)</option>
                        <option value="price_desc">Precio (mayor a menor)</option>
                        <option value="newest">Más recientes</option>
                    </select>
                </div>

                <div class="filter-group search-box">
                    <label for="search-input">
                        <i class="bi bi-search"></i> Buscar
                    </label>
                    <input type="text" id="search-input" placeholder="Buscar proyectos...">
                    <i class="bi bi-search"></i>
                </div>
            </div>
        </div>

        <!-- Categorías (Grid de iconos) -->
        <?php if (!empty($categories)): ?>
        <div class="categories-section">
            <h2 style="margin-bottom: 20px;">Explorar por Categoría</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                <div class="category-card" data-category="<?php echo $cat['category_key']; ?>">
                    <div class="category-icon" style="color: <?php echo $cat['category_color']; ?>">
                        <i class="bi <?php echo $cat['category_icon']; ?>"></i>
                    </div>
                    <div class="category-name"><?php echo $cat['category_name']; ?></div>
                    <div class="category-count">
                        <?php echo $categoryCounts[$cat['category_key']] ?? 0; ?> proyectos
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Proyectos Destacados -->
        <?php if (!empty($featuredProjects)): ?>
        <div style="margin: 50px 0;">
            <h2 style="margin-bottom: 20px;">
                <i class="bi bi-star-fill" style="color: var(--mizton-orange);"></i>
                Proyectos Destacados
            </h2>
            <div class="projects-grid">
                <?php foreach ($featuredProjects as $project): 
                    $categoryInfo = $MARKETPLACE_CATEGORIES[$project['category']] ?? $MARKETPLACE_CATEGORIES['otro'];
                    $statusInfo = $PROJECT_STATUSES[$project['status']] ?? $PROJECT_STATUSES['desarrollo'];
                    $imageUrl = $project['main_image_url'] ?: '/marketplace/assets/images/placeholder-project.jpg';
                ?>
                <div class="project-card featured">
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         alt="<?php echo htmlspecialchars($project['name']); ?>" 
                         class="project-image"
                         onerror="this.src='/marketplace/assets/images/placeholder-project.jpg'">
                    
                    <div class="project-content">
                        <div class="project-header">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                            <span class="project-code"><?php echo htmlspecialchars($project['project_code']); ?></span>
                        </div>

                        <div class="project-category" style="background: <?php echo $categoryInfo['color']; ?>20; color: <?php echo $categoryInfo['color']; ?>;">
                            <i class="bi <?php echo $categoryInfo['icon']; ?>"></i>
                            <span><?php echo $categoryInfo['name']; ?></span>
                        </div>

                        <p class="project-description">
                            <?php echo htmlspecialchars($project['short_description'] ?: substr($project['description'], 0, 150) . '...'); ?>
                        </p>

                        <?php if ($project['funding_percentage'] > 0): ?>
                        <div class="funding-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?php echo min($project['funding_percentage'], 100); ?>%"></div>
                            </div>
                            <div class="progress-info">
                                <span><?php echo formatCurrency($project['funding_raised']); ?> recaudado</span>
                                <span class="progress-percentage"><?php echo formatPercentage($project['funding_percentage']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="project-stats">
                            <div class="stat-item">
                                <div class="stat-label">Precio Token</div>
                                <div class="stat-value highlight"><?php echo formatCurrency($project['token_price_usd']); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-label">APY/ROI</div>
                                <div class="stat-value"><?php echo $project['apy_percentage'] ? formatPercentage($project['apy_percentage']) : 'N/A'; ?></div>
                            </div>
                        </div>

                        <div class="project-footer">
                            <span class="project-status status-<?php echo $project['status']; ?>">
                                <i class="bi <?php echo $statusInfo['icon']; ?>"></i>
                                <?php echo $statusInfo['label']; ?>
                            </span>
                            <a href="/marketplace/project.php?slug=<?php echo $project['slug']; ?>" 
                               class="btn-view-project">
                                Ver Más
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Todos los Proyectos -->
        <div id="projects-section" style="margin-top: 50px;">
            <h2 style="margin-bottom: 20px;">Todos los Proyectos</h2>
            <div id="projects-grid" class="projects-grid">
                <!-- Los proyectos se cargarán dinámicamente vía JavaScript -->
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 20px; color: #666;">Cargando proyectos...</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer style="background: var(--mizton-dark); color: white; padding: 40px 0; margin-top: 80px;">
        <div class="marketplace-container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
                <div>
                    <h3 style="margin-bottom: 15px;">Mizton Marketplace</h3>
                    <p style="opacity: 0.8; line-height: 1.6;">
                        Plataforma de proyectos tokenizados de activos reales.
                        Invierte en el futuro de la economía digital.
                    </p>
                </div>
                <div>
                    <h4 style="margin-bottom: 15px;">Enlaces Rápidos</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px;"><a href="/" style="color: rgba(255,255,255,0.8); text-decoration: none;">Inicio</a></li>
                        <li style="margin-bottom: 10px;"><a href="/marketplace" style="color: rgba(255,255,255,0.8); text-decoration: none;">Marketplace</a></li>
                        <li style="margin-bottom: 10px;"><a href="/panel" style="color: rgba(255,255,255,0.8); text-decoration: none;">Panel de Usuario</a></li>
                        <?php if (isMarketplaceAdmin()): ?>
                        <li style="margin-bottom: 10px;"><a href="/marketplace/admin" style="color: var(--mizton-orange); text-decoration: none;">Admin Marketplace</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 15px;">Contacto</h4>
                    <p style="opacity: 0.8;">
                        <i class="bi bi-envelope"></i> 
                        <?php echo getMarketplaceConfig('contact_email', 'marketplace@mizton.cat'); ?>
                    </p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
                <p style="opacity: 0.6;">&copy; <?php echo date('Y'); ?> Mizton. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="/marketplace/assets/js/marketplace.js"></script>

</body>
</html>
