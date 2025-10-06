<?php
// Post individual del blog/news
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
            file_put_contents($logFile, "[$timestamp] NEWS DEBUG: Referido válido guardado: " . $_SESSION['referido'] . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] NEWS DEBUG: URL original: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
        }
    }
}

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

// Configurar SEO con URLs limpias
$pageTitle = $post['title'] . ' - Mizton News';
$pageDescription = $post['excerpt'];
$pageImage = $post['image'];

// URL limpia para compartir (con referido si existe)
$currentRef = $_SESSION['referido'] ?? '';
if (!empty($currentRef)) {
    $pageUrl = 'https://mizton.cat/news/' . $post['slug'] . '/' . $currentRef;
    $shareUrl = $pageUrl;
} else {
    $pageUrl = 'https://mizton.cat/news/' . $post['slug'];
    $shareUrl = $pageUrl;
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
    <meta property="og:url" content="<?php echo $shareUrl; ?>">
    <meta property="og:type" content="article">
    <meta property="article:author" content="<?php echo $post['author']; ?>">
    <meta property="article:published_time" content="<?php echo $post['published_at']; ?>">
    <meta property="article:section" content="<?php echo $post['category']; ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo $pageImage; ?>">
    <meta name="twitter:url" content="<?php echo $shareUrl; ?>">
    
    <!-- WhatsApp -->
    <meta property="og:locale" content="es_ES">
    
    <link rel="stylesheet" href="assets/blog-styles.css">
    <link rel="stylesheet" href="assets/post-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo $pageUrl; ?>">
    
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
                <a href="index.php">Todos los Posts</a>
                <a href="../index.php">Inicio</a>
                <?php if (!empty($currentRef)): ?>
                <a href="../index.php" class="cta-nav">Únete con <?php echo strtoupper($currentRef); ?></a>
                <?php else: ?>
                <a href="../index.php#unirse" class="cta-nav">Únete Ahora</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Post Content -->
    <article class="post-single">
        <div class="container">
            <!-- Post Header -->
            <header class="post-header">
                <div class="post-meta">
                    <span class="category"><?php echo htmlspecialchars($post['category']); ?></span>
                    <span class="date"><?php echo date('d M Y', strtotime($post['published_at'])); ?></span>
                    <span class="reading-time"><?php echo $post['reading_time'] ?? $post['read_time'] ?? '5'; ?> min lectura</span>
                </div>
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <p class="post-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                
                <!-- Referral Info -->
                <?php if (!empty($currentRef)): ?>
                <div class="referral-info">
                    <i class="fas fa-user-friends"></i>
                    <span>Compartido por: <strong><?php echo strtoupper($currentRef); ?></strong></span>
                </div>
                <?php endif; ?>
            </header>

            <!-- Post Image -->
            <?php if ($post['image']): ?>
            <div class="post-image">
                <img src="<?php echo $post['image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
            <?php endif; ?>

            <!-- Post Content -->
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>

            <!-- Social Share -->
            <div class="social-share">
                <h4>Comparte este artículo</h4>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($shareUrl); ?>" target="_blank" class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($shareUrl); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-btn twitter">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . $shareUrl); ?>" target="_blank" class="share-btn whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($shareUrl); ?>" target="_blank" class="share-btn linkedin">
                        <i class="fab fa-linkedin-in"></i> LinkedIn
                    </a>
                </div>
                
                <!-- Copy URL -->
                <div class="copy-url">
                    <input type="text" id="shareUrl" value="<?php echo $shareUrl; ?>" readonly>
                    <button onclick="copyUrl()" class="copy-btn">Copiar URL</button>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="post-cta">
                <div class="cta-content">
                    <h3>¿Te gustó este artículo?</h3>
                    <p>Únete a Mizton y forma parte del futuro financiero</p>
                    <?php if (!empty($currentRef)): ?>
                    <a href="../index.php" class="cta-button">Únete con <?php echo strtoupper($currentRef); ?></a>
                    <?php else: ?>
                    <a href="../index.php#unirse" class="cta-button">Únete Ahora</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>

    <!-- Related Posts -->
    <?php if (!empty($relatedPosts)): ?>
    <section class="related-posts">
        <div class="container">
            <h3>Artículos Relacionados</h3>
            <div class="posts-grid">
                <?php foreach ($relatedPosts as $relatedPost): ?>
                <article class="post-card">
                    <a href="<?php echo !empty($currentRef) ? $relatedPost['slug'] . '/' . $currentRef : $relatedPost['slug']; ?>">
                        <?php if ($relatedPost['image']): ?>
                        <div class="post-image">
                            <img src="<?php echo $relatedPost['image']; ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="post-info">
                            <span class="category"><?php echo $relatedPost['category']; ?></span>
                            <h4><?php echo htmlspecialchars($relatedPost['title']); ?></h4>
                            <p><?php echo htmlspecialchars($relatedPost['excerpt']); ?></p>
                            <div class="post-meta">
                                <span><?php echo date('d M Y', strtotime($relatedPost['published_at'])); ?></span>
                                <span><?php echo $relatedPost['reading_time']; ?> min</span>
                            </div>
                        </div>
                    </a>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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

    <script>
        function copyUrl() {
            const urlInput = document.getElementById('shareUrl');
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            
            const copyBtn = document.querySelector('.copy-btn');
            const originalText = copyBtn.textContent;
            copyBtn.textContent = '¡Copiado!';
            copyBtn.style.background = '#28a745';
            
            setTimeout(() => {
                copyBtn.textContent = originalText;
                copyBtn.style.background = '';
            }, 2000);
        }
    </script>
</body>
</html>
