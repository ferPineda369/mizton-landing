<?php
/**
 * Funciones del Blog Mizton
 * Funciones para manejo de posts, categorías y utilidades
 */

/**
 * Obtener posts del blog
 */
function getBlogPosts($limit = 10, $offset = 0, $category = null, $status = 'published') {
    $db = getBlogDB();
    
    $sql = "SELECT * FROM blog_posts";
    $params = [];
    
    // Agregar filtro de status solo si no es 'all'
    if ($status !== 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    if ($category) {
        if ($status === 'all') {
            $sql .= " WHERE category = ?";
        } else {
            $sql .= " AND category = ?";
        }
        $params[] = $category;
    }
    
    $sql .= " ORDER BY published_at DESC, created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar tags JSON
    foreach ($posts as &$post) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
        $post['image'] = $post['image'] ?: 'assets/images/default-post.jpg';
    }
    
    return $posts;
}

/**
 * Obtener post destacado
 */
function getFeaturedPost() {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE featured = 1 AND status = 'published' ORDER BY published_at DESC LIMIT 1");
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
        $post['image'] = $post['image'] ?: 'assets/images/default-post.jpg';
    }
    
    return $post;
}

/**
 * Obtener post por slug
 */
function getPostBySlug($slug) {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
        $post['image'] = $post['image'] ?: 'assets/images/default-post.jpg';
        
        // Incrementar vistas
        incrementPostViews($post['id']);
    }
    
    return $post;
}

/**
 * Incrementar vistas de un post
 */
function incrementPostViews($postId) {
    $db = getBlogDB();
    $stmt = $db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$postId]);
}

/**
 * Obtener total de posts
 */
function getTotalPosts($status = 'published') {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = ?");
    $stmt->execute([$status]);
    return $stmt->fetchColumn();
}

/**
 * Obtener posts relacionados
 */
function getRelatedPosts($currentPostId, $category, $limit = 3) {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id != ? AND category = ? AND status = 'published' ORDER BY RAND() LIMIT ?");
    $stmt->execute([$currentPostId, $category, $limit]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($posts as &$post) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
        $post['image'] = $post['image'] ?: 'assets/images/default-post.jpg';
    }
    
    return $posts;
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd M Y') {
    // Verificar si la fecha es válida
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return 'Fecha no disponible';
    }
    
    $months = [
        'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
        'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
        'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
    ];
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'Fecha inválida';
    }
    
    $formatted = date($format, $timestamp);
    return str_replace(array_keys($months), array_values($months), $formatted);
}

/**
 * Generar slug desde título
 */
function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Verificar unicidad
    $db = getBlogDB();
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Calcular tiempo de lectura
 */
function calculateReadTime($content) {
    $wordCount = str_word_count(strip_tags($content));
    $readTime = ceil($wordCount / 200); // 200 palabras por minuto
    return max(1, $readTime);
}

/**
 * Obtener categorías con conteo
 */
function getCategoriesWithCount() {
    global $blog_categories;
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' GROUP BY category");
    $stmt->execute();
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $result = [];
    foreach ($blog_categories as $key => $category) {
        $result[$key] = $category;
        $result[$key]['count'] = $counts[$key] ?? 0;
    }
    
    return $result;
}

/**
 * Suscribir email al newsletter
 */
function subscribeNewsletter($email) {
    $db = getBlogDB();
    
    try {
        $stmt = $db->prepare("INSERT INTO blog_newsletter (email) VALUES (?) ON DUPLICATE KEY UPDATE status = 'active'");
        $stmt->execute([$email]);
        return ['success' => true, 'message' => 'Suscripción exitosa'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error al suscribir'];
    }
}

/**
 * Crear post de ejemplo
 */
function createSamplePosts() {
    $db = getBlogDB();
    
    // Verificar si ya hay posts
    $stmt = $db->prepare("SELECT COUNT(*) FROM blog_posts");
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        return; // Ya hay posts
    }
    
    $samplePosts = [
        [
            'title' => 'El Futuro de la Tokenización RWA: Transformando Activos Reales',
            'content' => '<p>La tokenización de activos del mundo real (RWA) está revolucionando la forma en que invertimos y gestionamos activos. Desde propiedades inmobiliarias hasta obras de arte, todo puede ser tokenizado.</p><p>Esta tecnología permite fraccionar activos de alto valor, democratizando el acceso a inversiones que antes estaban reservadas para grandes capitales.</p><h2>Beneficios de la Tokenización</h2><ul><li>Liquidez mejorada</li><li>Acceso democratizado</li><li>Transparencia blockchain</li><li>Costos reducidos</li></ul>',
            'category' => 'rwa',
            'tags' => '["tokenización", "blockchain", "inversiones", "RWA"]',
            'featured' => 1,
            'image' => 'assets/images/rwa-tokenization.jpg'
        ],
        [
            'title' => 'Blockchain: La Tecnología que Está Cambiando las Finanzas',
            'content' => '<p>La tecnología blockchain ha evolucionado más allá de las criptomonedas, transformando sectores enteros de la economía global.</p><p>Desde contratos inteligentes hasta sistemas de pagos descentralizados, blockchain ofrece transparencia, seguridad y eficiencia sin precedentes.</p>',
            'category' => 'blockchain',
            'tags' => '["blockchain", "fintech", "criptomonedas", "tecnología"]',
            'featured' => 0,
            'image' => 'assets/images/blockchain-tech.jpg'
        ],
        [
            'title' => 'Fintech 2024: Tendencias que Definirán el Futuro Financiero',
            'content' => '<p>El sector fintech continúa innovando con soluciones que simplifican y democratizan los servicios financieros.</p><p>Inteligencia artificial, pagos digitales y banca descentralizada son solo algunas de las tendencias que marcarán 2024.</p>',
            'category' => 'fintech',
            'tags' => '["fintech", "innovación", "pagos digitales", "IA"]',
            'featured' => 0,
            'image' => 'assets/images/fintech-trends.jpg'
        ]
    ];
    
    foreach ($samplePosts as $post) {
        $slug = generateSlug($post['title']);
        $excerpt = substr(strip_tags($post['content']), 0, 150) . '...';
        $readTime = calculateReadTime($post['content']);
        
        $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, featured, read_time, image, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', NOW())");
        $stmt->execute([
            $post['title'],
            $slug,
            $excerpt,
            $post['content'],
            $post['category'],
            $post['tags'],
            $post['featured'],
            $readTime,
            $post['image']
        ]);
    }
}

// Crear posts de ejemplo si no existen
createSamplePosts();
?>
