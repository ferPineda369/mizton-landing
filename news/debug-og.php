<?php
// Debug Open Graph - Verificar qué ve el scraper de Facebook
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
    die('Slug requerido');
}

// Obtener post
$post = getPostBySlug($slug);

if (!$post) {
    die('Post no encontrado');
}

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

// Información de debug
$debugInfo = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'slug' => $slug,
    'current_ref' => $currentRef,
    'share_ref' => $shareRef,
    'canonical_url' => $canonicalUrl,
    'page_url' => $pageUrl,
    'post_title' => $post['title'],
    'post_image' => $pageImage,
    'is_facebook_scraper' => strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'facebookexternalhit') !== false,
    'is_whatsapp_scraper' => strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'WhatsApp') !== false
];

header('Content-Type: text/html; charset=utf-8');
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
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .debug-info h3 { margin-top: 0; }
        .debug-info pre { background: white; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .meta-tags { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .meta-tags h3 { margin-top: 0; color: #1877f2; }
    </style>
</head>
<body>
    <h1>Debug Open Graph - <?php echo htmlspecialchars($post['title']); ?></h1>
    
    <div class="debug-info">
        <h3>🔍 Información de Debug</h3>
        <pre><?php echo json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
    </div>
    
    <div class="meta-tags">
        <h3>📋 Meta Tags Generados</h3>
        <p><strong>og:title:</strong> <?php echo htmlspecialchars($post['title']); ?></p>
        <p><strong>og:description:</strong> <?php echo htmlspecialchars($pageDescription); ?></p>
        <p><strong>og:image:</strong> <?php echo $pageImage; ?></p>
        <p><strong>og:url:</strong> <?php echo $pageUrl; ?></p>
        <p><strong>canonical:</strong> <?php echo $pageUrl; ?></p>
    </div>
    
    <div class="debug-info">
        <h3>🧪 URLs de Prueba</h3>
        <p><strong>URL Canónica:</strong> <a href="<?php echo $canonicalUrl; ?>" target="_blank"><?php echo $canonicalUrl; ?></a></p>
        <?php if (!empty($shareRef)): ?>
        <p><strong>URL con Referido:</strong> <a href="<?php echo $canonicalUrl . '/' . $shareRef; ?>" target="_blank"><?php echo $canonicalUrl . '/' . $shareRef; ?></a></p>
        <?php endif; ?>
        
        <h4>🔗 Herramientas de Debug:</h4>
        <p><a href="https://developers.facebook.com/tools/debug/?q=<?php echo urlencode($pageUrl); ?>" target="_blank">Facebook Sharing Debugger</a></p>
        <p><a href="https://cards-dev.twitter.com/validator?url=<?php echo urlencode($pageUrl); ?>" target="_blank">Twitter Card Validator</a></p>
    </div>
    
    <div class="debug-info">
        <h3>📄 Contenido del Post</h3>
        <h4><?php echo htmlspecialchars($post['title']); ?></h4>
        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
        <?php if (!empty($post['image'])): ?>
        <img src="<?php echo $pageImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width: 300px; height: auto;">
        <?php endif; ?>
    </div>
</body>
</html>
