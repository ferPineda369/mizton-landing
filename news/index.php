<?php
// Página principal del News/Blog
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
        
        // Debug: verificar que la variable se está guardando
        $logFile = dirname(__DIR__) . '/landing_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        if (is_writable(dirname($logFile))) {
            file_put_contents($logFile, "[$timestamp] NEWS INDEX DEBUG: Referido válido guardado: " . $_SESSION['referido'] . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] NEWS INDEX DEBUG: URL original: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
        }
    }
}

// Obtener posts
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$postsPerPage = 6;

if ($search) {
    $posts = searchPosts($search, $page, $postsPerPage);
    $totalPosts = getTotalSearchResults($search);
} elseif ($category) {
    $posts = getPostsByCategory($category, $page, $postsPerPage);
    $totalPosts = getTotalPostsByCategory($category);
} else {
    $posts = getAllPosts($page, $postsPerPage);
    $totalPosts = getTotalPosts();
}

$totalPages = ceil($totalPosts / $postsPerPage);
$categories = getCategories();

// Obtener referido actual para URLs
$currentRef = $_SESSION['referido'] ?? '';

// Configurar SEO
$pageTitle = 'Mizton News - Noticias y Análisis Financiero';
$pageDescription = 'Mantente informado sobre las últimas tendencias en blockchain, fintech, tokenización RWA y el futuro de las finanzas digitales.';

if ($category) {
    $pageTitle = "Categoría: " . ucfirst($category) . " - Mizton News";
    $pageDescription = "Artículos sobre " . $category . " en Mizton News";
}

if ($search) {
    $pageTitle = "Búsqueda: " . htmlspecialchars($search) . " - Mizton News";
    $pageDescription = "Resultados de búsqueda para: " . htmlspecialchars($search);
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
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:image" content="https://mizton.cat/social-preview.jpg">
    <meta property="og:url" content="https://mizton.cat/news/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Mizton News">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="https://mizton.cat/social-preview.jpg">
    
    <link rel="stylesheet" href="assets/css/blog.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://mizton.cat/news/">
    
    <!-- Meta Pixel Code -->
    <script src="../fb-proxy.js"></script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=684765634652448&ev=PageView&noscript=1"
    /></noscript>
</head>
<body>
    <!-- Navigation -->
    <nav class="blog-nav">
        <div class="container">
            <div class="nav-brand">
                <a href="../index.php">
                    <img src="../logo.gif" alt="Mizton" class="logo">
                    <span>Mizton News</span>
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php" class="active">Todos los Posts</a>
                <a href="../index.php">Inicio</a>
                <?php if (!empty($currentRef)): ?>
                <a href="../index.php" class="cta-nav">Únete con <?php echo strtoupper($currentRef); ?></a>
                <?php else: ?>
                <a href="../index.php#unirse" class="cta-nav">Únete Ahora</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="blog-header">
        <div class="container">
            <div class="header-content">
                <h1>Mizton News</h1>
                <p>Noticias, análisis y tendencias del mundo financiero digital</p>
                
                <!-- Referral Info -->
                <?php if (!empty($currentRef)): ?>
                <div class="referral-info">
                    <i class="fas fa-user-friends"></i>
                    <span>Navegando con referido: <strong><?php echo strtoupper($currentRef); ?></strong></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Search and Filters -->
    <section class="blog-filters">
        <div class="container">
            <div class="filters-content">
                <!-- Search -->
                <div class="search-box">
                    <form method="GET" action="">
                        <?php if (!empty($currentRef)): ?>
                        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($currentRef); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Buscar artículos..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <!-- Categories -->
                <div class="categories">
                    <a href="<?php echo !empty($currentRef) ? '?ref=' . $currentRef : ''; ?>" class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">
                        Todos
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?php echo urlencode($cat); ?><?php echo !empty($currentRef) ? '&ref=' . $currentRef : ''; ?>" 
                       class="category-btn <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php echo ucfirst($cat); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Posts Grid -->
    <main class="blog-main">
        <div class="container">
            <?php if ($search): ?>
            <div class="search-results">
                <h2>Resultados para: "<?php echo htmlspecialchars($search); ?>"</h2>
                <p><?php echo $totalPosts; ?> artículo<?php echo $totalPosts !== 1 ? 's' : ''; ?> encontrado<?php echo $totalPosts !== 1 ? 's' : ''; ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($posts)): ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <!-- Usar URLs limpias con referido opcional -->
                    <a href="<?php echo !empty($currentRef) ? htmlspecialchars($post['slug']) . '/' . $currentRef : htmlspecialchars($post['slug']); ?>">
                        <?php if ($post['image']): ?>
                        <div class="post-image">
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="post-content">
                            <div class="post-meta">
                                <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <span class="date"><?php echo date('d M Y', strtotime($post['published_at'])); ?></span>
                                <span class="reading-time"><?php echo $post['reading_time']; ?> min</span>
                            </div>
                            <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <div class="post-footer">
                                <span class="read-more">Leer más <i class="fas fa-arrow-right"></i></span>
                                <div class="post-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($currentRef) ? '&ref=' . $currentRef : ''; ?>" class="pagination-btn">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>

                <div class="pagination-numbers">
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($currentRef) ? '&ref=' . $currentRef : ''; ?>" 
                       class="pagination-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($currentRef) ? '&ref=' . $currentRef : ''; ?>" class="pagination-btn">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="no-posts">
                <div class="no-posts-content">
                    <i class="fas fa-search"></i>
                    <h3>No se encontraron artículos</h3>
                    <p><?php echo $search ? 'Intenta con otros términos de búsqueda' : 'Aún no hay artículos en esta categoría'; ?></p>
                    <a href="<?php echo !empty($currentRef) ? '?ref=' . $currentRef : ''; ?>" class="btn btn-primary">Ver todos los artículos</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h3>Mantente Informado</h3>
                    <p>Recibe las últimas noticias y análisis directamente en tu email</p>
                </div>
                <div class="newsletter-form">
                    <form id="newsletterForm">
                        <input type="email" id="newsletterEmail" placeholder="Tu email" required>
                        <?php if (!empty($currentRef)): ?>
                        <input type="hidden" id="newsletterRef" value="<?php echo htmlspecialchars($currentRef); ?>">
                        <?php endif; ?>
                        <button type="submit">Suscribirse</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="blog-cta">
        <div class="container">
            <div class="cta-content">
                <h3>¿Te gustó nuestro contenido?</h3>
                <p>Únete a Mizton y forma parte del futuro financiero</p>
                <?php if (!empty($currentRef)): ?>
                <a href="../index.php" class="cta-button">Únete con <?php echo strtoupper($currentRef); ?></a>
                <?php else: ?>
                <a href="../index.php#unirse" class="cta-button">Únete Ahora</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="blog-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="../logo.gif" alt="Mizton" class="logo">
                    <span>Mizton News</span>
                </div>
                <div class="footer-links">
                    <a href="../index.php">Inicio</a>
                    <a href="index.php">Todos los Posts</a>
                    <a href="../index.php#unirse">Únete</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mizton. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/blog.js"></script>
    <script>
        // Newsletter form
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletterEmail').value;
            const ref = document.getElementById('newsletterRef')?.value || '';
            
            fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    referral_code: ref
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Gracias por suscribirte!');
                    document.getElementById('newsletterEmail').value = '';
                } else {
                    alert('Error al suscribirse. Inténtalo de nuevo.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al suscribirse. Inténtalo de nuevo.');
            });
        });
    </script>
</body>
</html>
