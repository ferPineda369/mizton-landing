<?php
// Blog principal de Mizton - Tecnología y Innovación
require_once 'config/blog-config.php';
require_once 'includes/blog-functions.php';

// Obtener posts recientes
$posts = getBlogPosts(6); // Últimos 6 posts
$featuredPost = getFeaturedPost();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Mizton - Tecnología e Innovación Financiera</title>
    <meta name="description" content="Descubre las últimas tendencias en tecnología blockchain, fintech y tokenización RWA. El futuro de las finanzas digitales.">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Blog Mizton - Tecnología e Innovación">
    <meta property="og:description" content="Tendencias en blockchain, fintech y tokenización RWA">
    <meta property="og:image" content="https://mizton.cat/blog/assets/blog-social.jpg">
    <meta property="og:url" content="https://mizton.cat/blog/">
    
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
                        <span class="brand-text">Mizton <span class="blog-label">Blog</span></span>
                    </a>
                </div>
                <div class="nav-links">
                    <a href="#tecnologia">Tecnología</a>
                    <a href="#blockchain">Blockchain</a>
                    <a href="#fintech">Fintech</a>
                    <a href="#rwa">Tokenización</a>
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
                        <span class="stat-number">2024</span>
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
                        <span class="date"><?php echo formatDate($featuredPost['date']); ?></span>
                        <span class="read-time"><?php echo $featuredPost['read_time']; ?> min</span>
                    </div>
                    <h2 class="featured-title">
                        <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>">
                            <?php echo htmlspecialchars($featuredPost['title']); ?>
                        </a>
                    </h2>
                    <p class="featured-excerpt">
                        <?php echo htmlspecialchars($featuredPost['excerpt']); ?>
                    </p>
                    <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>" class="read-more">
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
                            <span class="date"><?php echo formatDate($post['date']); ?></span>
                            <span class="read-time"><?php echo $post['read_time']; ?> min</span>
                        </div>
                        <h3 class="post-title">
                            <a href="post.php?slug=<?php echo $post['slug']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="post-excerpt">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>
                        <div class="post-footer">
                            <a href="post.php?slug=<?php echo $post['slug']; ?>" class="read-more-link">
                                Leer más
                            </a>
                            <div class="post-tags">
                                <?php foreach ($post['tags'] as $tag): ?>
                                <span class="tag"><?php echo $tag; ?></span>
                                <?php endforeach; ?>
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
                    <a href="../">Inicio</a>
                    <a href="../#unirse">Únete</a>
                    <a href="../#contacto">Contacto</a>
                    <a href="admin/">Admin</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Mizton Blog. Tecnología e Innovación.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/blog-scripts.js"></script>
</body>
</html>
