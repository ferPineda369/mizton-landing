<?php
// News principal de Mizton - Tecnología y Innovación
require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manejo de códigos de referido - validación mejorada
if (isset($_GET['ref'])) {
    $referido = preg_replace('/[^a-z0-9]/', '', strtolower($_GET['ref']));
    
    // Validar que el código tenga exactamente 6 caracteres alfanuméricos
    if (strlen($referido) === 6 && ctype_alnum($referido)) {
        $_SESSION['referido'] = $referido;
    }
}

// Obtener referido actual para URLs
$currentRef = $_SESSION['referido'] ?? '';

// Obtener posts recientes
$posts = getBlogPosts(6); // Últimos 6 posts
$featuredPost = getFeaturedPost();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mizton News - Tecnología e Innovación Financiera</title>
    <meta name="description" content="Descubre las últimas tendencias en tecnología blockchain, fintech y tokenización RWA. El futuro de las finanzas digitales.">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Mizton News - Tecnología e Innovación">
    <meta property="og:description" content="Tendencias en blockchain, fintech y tokenización RWA">
    <meta property="og:image" content="https://mizton.cat/news/assets/blog-social.jpg">
    <meta property="og:url" content="https://mizton.cat/news/">
    
    <!-- Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/blog-styles.css">
    
    <!-- Facebook Pixel -->
    <script src="../fb-proxy.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="blog-header">
        <nav class="blog-nav">
            <div class="container">
                <div class="nav-brand">
                    <a href="../">
                        <img src="../logo.gif" alt="Mizton" class="logo">
                        <span class="brand-text">Mizton <span class="blog-label">News</span></span>
                    </a>
                </div>
                <div class="nav-links">
                    <a href="#tecnologia">Tecnología</a>
                    <a href="#blockchain">Blockchain</a>
                    <a href="#fintech">Fintech</a>
                    <a href="#rwa">Tokenización</a>
                    <?php if (!empty($currentRef)): ?>
                    <a href="https://mizton.cat/<?php echo $currentRef; ?>" class="cta-link" target="_blank">Únete</a>
                    <?php else: ?>
                    <a href="https://mizton.cat/" class="cta-link" target="_blank">Únete</a>
                    <?php endif; ?>
                </div>
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <span class="title-main">Tecnología que</span>
                    <span class="title-highlight">Transforma el Futuro</span>
                </h1>
                <p class="hero-subtitle">
                    Explora las últimas tendencias en blockchain, fintech y tokenización RWA. 
                    Mantente al día con la revolución tecnológica que está cambiando las finanzas globales.
                </p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo getTotalPosts(); ?></span>
                        <span class="stat-label">Artículos</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">5</span>
                        <span class="stat-label">Categorías</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">2025</span>
                        <span class="stat-label">Actualizado</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Post -->
    <?php if ($featuredPost): ?>
    <section class="featured-post">
        <div class="container">
            <div class="featured-card">
                <div class="featured-image">
                    <img src="<?php echo $featuredPost['image']; ?>" alt="<?php echo htmlspecialchars($featuredPost['title']); ?>">
                    <div class="featured-badge">Destacado</div>
                </div>
                <div class="featured-content">
                    <div class="post-meta">
                        <span class="category"><?php echo $featuredPost['category']; ?></span>
                        <span class="date"><?php echo formatDate($featuredPost['published_at'] ?: $featuredPost['created_at']); ?></span>
                        <span class="read-time"><?php echo $featuredPost['read_time']; ?> min</span>
                    </div>
                    <h2 class="featured-title">
                        <a href="<?php echo !empty($currentRef) ? htmlspecialchars($featuredPost['slug']) . '/' . $currentRef : htmlspecialchars($featuredPost['slug']); ?>">
                            <?php echo htmlspecialchars($featuredPost['title']); ?>
                        </a>
                    </h2>
                    <p class="featured-excerpt">
                        <?php echo htmlspecialchars($featuredPost['excerpt']); ?>
                    </p>
                    <a href="<?php echo !empty($currentRef) ? htmlspecialchars($featuredPost['slug']) . '/' . $currentRef : htmlspecialchars($featuredPost['slug']); ?>" class="read-more">
                        Leer artículo completo <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Posts Grid -->
    <section class="posts-grid">
        <div class="container">
            <div class="section-header">
                <h2>Últimos Artículos</h2>
                <p>Mantente al día con las últimas tendencias tecnológicas</p>
            </div>
            
            <div class="posts-container">
                <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="post-image">
                        <img src="<?php echo $post['image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <div class="post-category"><?php echo $post['category']; ?></div>
                    </div>
                    <div class="post-content">
                        <div class="post-meta">
                            <span class="date"><?php echo formatDate($post['published_at'] ?: $post['created_at']); ?></span>
                            <span class="read-time"><?php echo $post['read_time']; ?> min</span>
                        </div>
                        <h3 class="post-title">
                            <a href="<?php echo !empty($currentRef) ? htmlspecialchars($post['slug']) . '/' . $currentRef : htmlspecialchars($post['slug']); ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="post-excerpt">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>
                        <div class="post-footer">
                            <a href="<?php echo !empty($currentRef) ? htmlspecialchars($post['slug']) . '/' . $currentRef : htmlspecialchars($post['slug']); ?>" class="read-more-link">
                                Leer más
                            </a>
                            <div class="post-tags">
                                <?php 
                                $tags = is_string($post['tags']) ? json_decode($post['tags'], true) : $post['tags'];
                                if (is_array($tags)):
                                    foreach ($tags as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; 
                                endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <div class="load-more-section">
                <button class="load-more-btn" onclick="loadMorePosts()">
                    <i class="fas fa-plus"></i>
                    Cargar más artículos
                </button>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Mantente Actualizado</h3>
                    <p>Recibe las últimas noticias sobre tecnología blockchain y fintech directamente en tu email.</p>
                </div>
                <div class="newsletter-form">
                    <form id="newsletter-form">
                        <input type="email" placeholder="Tu email" required>
                        <?php if (!empty($currentRef)): ?>
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($currentRef); ?>">
                        <?php endif; ?>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                            Suscribirse
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- CTA Content -->
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>¿Te gustó nuestro contenido?</h3>
                    <p>Únete a Mizton y forma parte del futuro financiero</p>
                </div>
                <div class="newsletter-form">
                    <?php if (!empty($currentRef)): ?>
                    <a href="https://mizton.cat/<?php echo $currentRef; ?>" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">Únete</a>
                    <?php else: ?>
                    <a href="https://mizton.cat/" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">Únete</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="blog-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="../logo.gif" alt="Mizton" class="footer-logo">
                    <span class="footer-brand-text">Mizton News</span>
                </div>
                <div class="footer-links">
                    <a href="https://mizton.cat/news/">Inicio</a>
                    <a href="https://mizton.cat/">Únete</a>
                    <a href="#contacto">Contacto</a>
                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                    <a href="admin/index.php">Admin</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mizton News. Tecnología e Innovación.</p>
            </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/blog-scripts.js"></script>
    <script>
        // Configurar código de referido para compartir
        window.userReferralCode = '<?php echo isset($_SESSION['userUser']) ? $_SESSION['userUser'] : ''; ?>';
        
        // Inicializar funcionalidades
        document.addEventListener('DOMContentLoaded', function() {
            initMobileMenu();
            initSearch();
            initNewsletter();
            loadMorePosts();
        });
    </script>
</body>
</html>
