<?php
// Post individual del news - Restaurado con soporte para referidos
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
        $_SESSION['userUser'] = $referido;
    }
}

// Obtener referido actual para URLs
$currentRef = $_SESSION['userUser'] ?? '';

// Obtener slug del post
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Obtener post
$post = getPostBySlug($slug);

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

$relatedPosts = getRelatedPosts($post['id'], $post['category'], 3);

// Configurar SEO
$pageTitle = $post['title'] . ' - Mizton News';
$pageDescription = $post['excerpt'];
// Construir URL de imagen correctamente
if (!empty($post['image'])) {
    // Si la imagen ya tiene el dominio completo, usarla tal como está
    if (strpos($post['image'], 'http') === 0) {
        $pageImage = $post['image'];
    } else {
        // Si es una ruta relativa, agregar el dominio
        $pageImage = "https://mizton.cat/news/" . ltrim($post['image'], '/');
    }
} else {
    $pageImage = "https://mizton.cat/logo.gif";
}

// Construir URL canónica del post (sin código de referido para Open Graph)
$canonicalUrl = "https://mizton.cat/news/" . $post['slug'];
$pageUrl = $canonicalUrl; // Para Open Graph siempre usar URL limpia

// Determinar qué código de referido usar para compartir (solo para JavaScript)
$shareRef = '';
if (isset($_SESSION['userUser']) && !empty($_SESSION['userUser'])) {
    // Si hay usuario logueado, usar su código
    $shareRef = $_SESSION['userUser'];
} elseif (!empty($currentRef)) {
    // Si no hay usuario logueado pero hay referido en la URL, usar ese
    $shareRef = $currentRef;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:image" content="<?php echo $pageImage; ?>">
    <meta property="og:url" content="<?php echo $pageUrl; ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Mizton News">
    <meta property="og:locale" content="es_ES">
    <meta property="article:author" content="<?php echo $post['author'] ?? 'Mizton Team'; ?>">
    <meta property="article:published_time" content="<?php echo $post['published_at']; ?>">
    <meta property="article:section" content="<?php echo $post['category']; ?>">
    
    <!-- Facebook específico -->
    <meta property="fb:app_id" content="1234567890123456">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@mizton">
    <meta name="twitter:creator" content="@mizton">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo $pageImage; ?>">
    
    <!-- WhatsApp específico -->
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($post['title']); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo $pageUrl; ?>">
    
    <!-- Alternate URLs para diferentes códigos de referido -->
    <?php if (!empty($currentRef)): ?>
    <link rel="alternate" href="<?php echo $canonicalUrl . '/' . $currentRef; ?>" hreflang="es">
    <?php endif; ?>
    
    <!-- Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/news/assets/blog-styles.css">
    <link rel="stylesheet" href="/news/assets/post-styles.css">
    
    <!-- Facebook Pixel -->
    <script src="/fb-proxy.js"></script>
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?php echo htmlspecialchars($post['title']); ?>",
        "description": "<?php echo htmlspecialchars($pageDescription); ?>",
        "image": "<?php echo $pageImage; ?>",
        "author": {
            "@type": "Organization",
            "name": "<?php echo $post['author'] ?? 'Mizton Team'; ?>"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Mizton",
            "logo": {
                "@type": "ImageObject",
                "url": "https://mizton.cat/logo.gif"
            }
        },
        "datePublished": "<?php echo $post['published_at']; ?>",
        "dateModified": "<?php echo $post['updated_at'] ?? $post['published_at']; ?>",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?php echo $pageUrl; ?>"
        }
    }
    </script>
</head>
<body>
    <!-- Header -->
    <header class="blog-header">
        <nav class="blog-nav">
            <div class="container">
                <div class="nav-brand">
                    <a href="/news/">
                        <img src="/logo.gif" alt="Mizton" class="logo">
                        <span class="brand-text">Mizton <span class="blog-label">News</span></span>
                    </a>
                </div>
                <div class="nav-links">
                    <a href="/news/">Inicio</a>
                    <a href="/news/#tecnologia">Tecnología</a>
                    <a href="/news/#blockchain">Blockchain</a>
                    <a href="/news/#fintech">Fintech</a>
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

    <!-- Post Header -->
    <section class="post-header">
        <div class="container">
            <div class="post-breadcrumb">
                <a href="/news/">News</a>
                <span class="separator">/</span>
                <span class="current"><?php echo htmlspecialchars($post['title']); ?></span>
            </div>
            
            <div class="post-hero">
                <div class="post-meta-header">
                    <span class="category"><?php echo $post['category']; ?></span>
                    <span class="date"><?php echo formatDate($post['published_at']); ?></span>
                    <span class="read-time"><?php echo $post['read_time'] ?? $post['reading_time'] ?? '5'; ?> min de lectura</span>
                    <span class="views"><?php echo number_format($post['views'] ?? 0); ?> vistas</span>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                
                <div class="post-author-info">
                    <div class="author-avatar">
                        <img src="/news/assets/images/mizton-team.png" alt="<?php echo $post['author'] ?? 'Mizton Team'; ?>">
                    </div>
                    <div class="author-details">
                        <span class="author-name"><?php echo $post['author'] ?? 'Mizton Team'; ?></span>
                        <span class="publish-date">Publicado el <?php echo formatDate($post['published_at'], 'd \d\e F \d\e Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Post Image -->
    <section class="post-image-section">
        <div class="container">
            <div class="post-featured-image">
                <img src="<?php echo $post['image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
        </div>
    </section>

    <!-- Post Content -->
    <article class="post-article">
        <div class="container">
            <div class="post-layout">
                <div class="post-content">
                    <?php echo $post['content']; ?>
                    
                    <!-- Tags -->
                    <?php 
                    $tags = is_string($post['tags']) ? json_decode($post['tags'], true) : $post['tags'];
                    if (!empty($tags) && is_array($tags)): ?>
                    <div class="post-tags-section">
                        <h4>Etiquetas:</h4>
                        <div class="post-tags">
                            <?php foreach ($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share Buttons -->
                    <div class="post-share">
                        <h4>Compartir este artículo:</h4>
                        <div class="share-buttons">
                            <button onclick="sharePost('<?php echo $pageUrl; ?>', '<?php echo htmlspecialchars($post['title']); ?>')" class="share-btn share-single">
                                <i class="fas fa-share-alt"></i>
                                Compartir
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Desktop -->
                <aside class="post-sidebar">
                    <!-- Newsletter -->
                    <div class="sidebar-widget newsletter-widget">
                        <h4>Mantente Actualizado</h4>
                        <p>Recibe las últimas noticias sobre tecnología blockchain y fintech.</p>
                        <form id="sidebar-newsletter-form">
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
                    
                    <!-- CTA Mizton -->
                    <div class="sidebar-widget cta-widget">
                        <h4>¿Interesado en Mizton?</h4>
                        <p>Descubre cómo puedes formar parte de la revolución financiera.</p>
                        <?php if (!empty($currentRef)): ?>
                        <a href="https://mizton.cat/<?php echo $currentRef; ?>" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">
                            <i class="fas fa-rocket"></i>
                            Únete
                        </a>
                        <?php else: ?>
                        <a href="https://mizton.cat/" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">
                            <i class="fas fa-rocket"></i>
                            Únete
                        </a>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </article>

    <!-- Sidebar Mobile (después de compartir) -->
    <section class="post-sidebar-mobile">
        <div class="container">
            <!-- Newsletter -->
            <div class="sidebar-widget newsletter-widget">
                <h4>Mantente Actualizado</h4>
                <p>Recibe las últimas noticias sobre tecnología blockchain y fintech.</p>
                <form id="sidebar-newsletter-form-mobile">
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
            
            <!-- CTA Mizton -->
            <div class="sidebar-widget cta-widget">
                <h4>¿Interesado en Mizton?</h4>
                <p>Descubre cómo puedes formar parte de la revolución financiera.</p>
                <?php if (!empty($currentRef)): ?>
                <a href="https://mizton.cat/<?php echo $currentRef; ?>" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">
                    <i class="fas fa-rocket"></i>
                    Únete
                </a>
                <?php else: ?>
                <a href="https://mizton.cat/" class="cta-button" target="_blank" style="display: flex !important; align-items: center !important; justify-content: center !important; gap: 8px !important; background: #40916C !important; color: #ffffff !important; text-decoration: none !important; padding: 12px 16px !important; border-radius: 6px !important; font-weight: 600 !important; border: none !important; min-height: 44px !important;">
                    <i class="fas fa-rocket"></i>
                    Únete
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <section class="related-posts">
        <div class="container">
            <div class="section-header">
                <h2>Artículos Relacionados</h2>
                <p>Continúa explorando temas similares</p>
            </div>
            
            <div class="related-posts-grid">
                <?php foreach ($relatedPosts as $relatedPost): ?>
                <article class="related-post-card">
                    <div class="related-post-image">
                        <img src="<?php echo $relatedPost['image']; ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                        <div class="post-category"><?php echo $relatedPost['category']; ?></div>
                    </div>
                    <div class="related-post-content">
                        <div class="post-meta">
                            <span class="date"><?php echo formatDate($relatedPost['published_at']); ?></span>
                            <span class="read-time"><?php echo $relatedPost['read_time'] ?? '5'; ?> min</span>
                        </div>
                        <h3 class="related-post-title">
                            <a href="<?php echo !empty($currentRef) ? '/news/' . htmlspecialchars($relatedPost['slug']) . '/' . $currentRef : '/news/' . htmlspecialchars($relatedPost['slug']); ?>">
                                <?php echo htmlspecialchars($relatedPost['title']); ?>
                            </a>
                        </h3>
                        <p class="related-post-excerpt">
                            <?php echo htmlspecialchars($relatedPost['excerpt']); ?>
                        </p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>¿Te gustó este artículo?</h3>
                    <p>Suscríbete para recibir más contenido sobre tecnología blockchain y fintech.</p>
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
        </div>
    </section>

    <!-- Footer -->
    <footer class="blog-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="/logo.gif" alt="Mizton" class="footer-logo">
                    <span class="footer-brand-text">Mizton News</span>
                </div>
                <div class="footer-links">
                    <a href="/news/">Inicio</a>
                    <a href="https://mizton.cat/">Únete</a>
                    <a href="#contacto">Contacto</a>
                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1): ?>
                    <a href="/news/admin/index.php">Admin</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mizton News. Tecnología e Innovación.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/news/assets/blog-scripts.js"></script>
    <script>
        // Configurar código de referido para compartir y URL base
        window.userReferralCode = '<?php echo $shareRef; ?>';
        window.basePostUrl = '<?php echo $canonicalUrl; ?>';
        
        // Tracking específico del post
        document.addEventListener('DOMContentLoaded', function() {
            // El blog-scripts.js ya inicializa las funciones básicas
            trackPostRead('<?php echo htmlspecialchars($post['title']); ?>', '<?php echo $post['category']; ?>');
        });
    </script>
</body>
</html>
