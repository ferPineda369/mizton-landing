<?php
// Post individual del blog
require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

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

// Obtener posts relacionados
$relatedPosts = getRelatedPosts($post['id'], $post['category'], 3);

// Configurar SEO
$pageTitle = $post['title'] . ' - Mizton Blog';
$pageDescription = $post['excerpt'];
$pageImage = $post['image'];
$pageUrl = BLOG_URL . 'post.php?slug=' . $post['slug'];
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
    <meta property="article:author" content="<?php echo $post['author']; ?>">
    <meta property="article:published_time" content="<?php echo $post['published_at']; ?>">
    <meta property="article:section" content="<?php echo $post['category']; ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo $pageImage; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo $pageUrl; ?>">
    
    <!-- Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/blog-styles.css">
    <link rel="stylesheet" href="assets/post-styles.css">
    
    <!-- Facebook Pixel -->
    <script src="../fb-proxy.js"></script>
    
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
            "name": "<?php echo $post['author']; ?>"
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
        "dateModified": "<?php echo $post['updated_at']; ?>",
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
                    <a href="index.php">
                        <img src="../logo.gif" alt="Mizton" class="logo">
                        <span class="brand-text">Mizton <span class="blog-label">Blog</span></span>
                    </a>
                </div>
                <div class="nav-links">
                    <a href="index.php">Inicio</a>
                    <a href="index.php#tecnologia">Tecnología</a>
                    <a href="index.php#blockchain">Blockchain</a>
                    <a href="index.php#fintech">Fintech</a>
                    <a href="../" class="cta-link">Volver a Mizton</a>
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
                <a href="index.php">Blog</a>
                <span class="separator">/</span>
                <span class="current"><?php echo htmlspecialchars($post['title']); ?></span>
            </div>
            
            <div class="post-hero">
                <div class="post-meta-header">
                    <span class="category"><?php echo $post['category']; ?></span>
                    <span class="date"><?php echo formatDate($post['published_at']); ?></span>
                    <span class="read-time"><?php echo $post['read_time']; ?> min de lectura</span>
                    <span class="views"><?php echo number_format($post['views']); ?> vistas</span>
                </div>
                
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="post-author-info">
                    <div class="author-avatar">
                        <img src="assets/images/mizton-team.jpg" alt="<?php echo $post['author']; ?>">
                    </div>
                    <div class="author-details">
                        <span class="author-name"><?php echo $post['author']; ?></span>
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
                    <?php if (!empty($post['tags'])): ?>
                    <div class="post-tags-section">
                        <h4>Etiquetas:</h4>
                        <div class="post-tags">
                            <?php foreach ($post['tags'] as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Share Buttons -->
                    <div class="post-share">
                        <h4>Compartir este artículo:</h4>
                        <div class="share-buttons">
                            <button onclick="sharePost('twitter', '<?php echo $pageUrl; ?>', '<?php echo htmlspecialchars($post['title']); ?>')" class="share-btn twitter">
                                <i class="fab fa-twitter"></i>
                                Twitter
                            </button>
                            <button onclick="sharePost('facebook', '<?php echo $pageUrl; ?>', '<?php echo htmlspecialchars($post['title']); ?>')" class="share-btn facebook">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </button>
                            <button onclick="sharePost('linkedin', '<?php echo $pageUrl; ?>', '<?php echo htmlspecialchars($post['title']); ?>')" class="share-btn linkedin">
                                <i class="fab fa-linkedin-in"></i>
                                LinkedIn
                            </button>
                            <button onclick="sharePost('whatsapp', '<?php echo $pageUrl; ?>', '<?php echo htmlspecialchars($post['title']); ?>')" class="share-btn whatsapp">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <aside class="post-sidebar">
                    <!-- Table of Contents -->
                    <div class="sidebar-widget toc-widget">
                        <h4>Contenido</h4>
                        <div id="table-of-contents">
                            <!-- Se genera dinámicamente con JavaScript -->
                        </div>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="sidebar-widget newsletter-widget">
                        <h4>Mantente Actualizado</h4>
                        <p>Recibe las últimas noticias sobre tecnología blockchain y fintech.</p>
                        <form id="sidebar-newsletter-form">
                            <input type="email" placeholder="Tu email" required>
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
                        <a href="../#unirse" class="cta-button">
                            <i class="fas fa-rocket"></i>
                            Únete Ahora
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </article>

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
                            <span class="read-time"><?php echo $relatedPost['read_time']; ?> min</span>
                        </div>
                        <h3 class="related-post-title">
                            <a href="post.php?slug=<?php echo $relatedPost['slug']; ?>">
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
                    <img src="../logo.gif" alt="Mizton" class="footer-logo">
                    <span class="footer-brand-text">Mizton Blog</span>
                </div>
                <div class="footer-links">
                    <a href="index.php">Blog</a>
                    <a href="../">Inicio</a>
                    <a href="../#unirse">Únete</a>
                    <a href="../#contacto">Contacto</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Mizton Blog. Tecnología e Innovación.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/blog-scripts.js"></script>
    <script src="assets/post-scripts.js"></script>
    
    <script>
        // Tracking específico del post
        document.addEventListener('DOMContentLoaded', function() {
            trackPostRead('<?php echo htmlspecialchars($post['title']); ?>', '<?php echo $post['category']; ?>');
        });
    </script>
</body>
</html>
