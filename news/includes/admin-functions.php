<?php
/**
 * Funciones adicionales para el panel de administración
 * Complementa blog-functions.php con funcionalidades específicas del admin
 */

/**
 * Crear nuevo post
 */
function createPost($data) {
    $db = getBlogDB();
    
    try {
        // Validar datos requeridos
        if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
            return ['success' => false, 'message' => 'Título, contenido y categoría son requeridos'];
        }
        
        // Generar slug único
        $slug = generateSlug($data['title']);
        
        // Procesar extracto
        $excerpt = !empty($data['excerpt']) ? $data['excerpt'] : substr(strip_tags($data['content']), 0, 150) . '...';
        
        // Procesar tags
        $tags = [];
        if (!empty($data['tags'])) {
            $tags = array_map('trim', explode(',', $data['tags']));
            $tags = array_filter($tags); // Remover vacíos
        }
        
        // Calcular tiempo de lectura
        $readTime = calculateReadTime($data['content']);
        
        // Preparar datos
        $title = trim($data['title']);
        $content = $data['content'];
        $category = $data['category'];
        $image = !empty($data['image']) ? $data['image'] : 'assets/images/default-post.jpg';
        $status = $data['status'] ?? 'draft';
        $featured = isset($data['featured']) ? 1 : 0;
        $author = 'Mizton Team';
        
        // Insertar en base de datos
        $sql = "INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, image, status, featured, read_time, author, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $title,
            $slug,
            $excerpt,
            $content,
            $category,
            json_encode($tags),
            $image,
            $status,
            $featured,
            $readTime,
            $author,
            $publishedAt
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Post creado exitosamente', 'post_id' => $db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Error al crear el post'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualizar post existente
 */
function updatePost($postId, $data) {
    $db = getBlogDB();
    
    try {
        // Validar que el post existe
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE id = ?");
        $stmt->execute([$postId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Post no encontrado'];
        }
        
        // Validar datos requeridos
        if (empty($data['title']) || empty($data['content']) || empty($data['category'])) {
            return ['success' => false, 'message' => 'Título, contenido y categoría son requeridos'];
        }
        
        // Generar nuevo slug si el título cambió
        $slug = generateSlugForUpdate($data['title'], $postId);
        
        // Procesar extracto
        $excerpt = !empty($data['excerpt']) ? $data['excerpt'] : substr(strip_tags($data['content']), 0, 150) . '...';
        
        // Procesar tags
        $tags = [];
        if (!empty($data['tags'])) {
            $tags = array_map('trim', explode(',', $data['tags']));
            $tags = array_filter($tags);
        }
        
        // Calcular tiempo de lectura
        $readTime = calculateReadTime($data['content']);
        
        // Preparar datos
        $title = trim($data['title']);
        $content = $data['content'];
        $category = $data['category'];
        $image = !empty($data['image']) ? $data['image'] : 'assets/images/default-post.jpg';
        $status = $data['status'] ?? 'draft';
        $featured = isset($data['featured']) ? 1 : 0;
        
        // Actualizar published_at si se está publicando por primera vez
        $publishedAt = null;
        if ($status === 'published') {
            $stmt = $db->prepare("SELECT published_at FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $currentPost = $stmt->fetch();
            $publishedAt = $currentPost['published_at'] ?: date('Y-m-d H:i:s');
        }
        
        // Actualizar en base de datos
        $sql = "UPDATE blog_posts SET title = ?, slug = ?, excerpt = ?, content = ?, category = ?, tags = ?, image = ?, status = ?, featured = ?, read_time = ?, updated_at = CURRENT_TIMESTAMP";
        $params = [$title, $slug, $excerpt, $content, $category, json_encode($tags), $image, $status, $featured, $readTime];
        
        if ($publishedAt) {
            $sql .= ", published_at = ?";
            $params[] = $publishedAt;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $postId;
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Post actualizado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar el post'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Eliminar post
 */
function deletePost($postId) {
    $db = getBlogDB();
    
    try {
        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $result = $stmt->execute([$postId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Post eliminado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el post'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Obtener post por ID para edición
 */
function getPostById($postId) {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        $post['tags'] = json_decode($post['tags'], true) ?: [];
    }
    
    return $post;
}

/**
 * Generar slug para actualización (evitar conflictos)
 */
function generateSlugForUpdate($title, $currentPostId) {
    $db = getBlogDB();
    
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $currentPostId]);
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Obtener total de vistas de todos los posts
 */
function getTotalViews() {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT SUM(views) FROM blog_posts WHERE status = 'published'");
    $stmt->execute();
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Obtener total de suscriptores del newsletter
 */
function getTotalSubscribers() {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM blog_newsletter WHERE status = 'active'");
    $stmt->execute();
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Obtener suscriptores del newsletter
 */
function getNewsletterSubscribers($limit = 50) {
    $db = getBlogDB();
    
    $stmt = $db->prepare("SELECT * FROM blog_newsletter ORDER BY subscribed_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Auto-guardar borrador
 */
function autoSaveDraft($data) {
    if (empty($data['title']) && empty($data['content'])) {
        return ['success' => false, 'message' => 'No hay contenido para guardar'];
    }
    
    $db = getBlogDB();
    
    try {
        // Buscar borrador existente por título o crear uno nuevo
        $draftId = null;
        
        if (!empty($data['title'])) {
            $stmt = $db->prepare("SELECT id FROM blog_posts WHERE title LIKE ? AND status = 'draft' ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute(['%' . $data['title'] . '%']);
            $existing = $stmt->fetch();
            if ($existing) {
                $draftId = $existing['id'];
            }
        }
        
        if ($draftId) {
            // Actualizar borrador existente
            $result = updatePost($draftId, array_merge($data, ['status' => 'draft']));
        } else {
            // Crear nuevo borrador
            $draftData = array_merge($data, [
                'title' => $data['title'] ?: 'Borrador ' . date('Y-m-d H:i:s'),
                'category' => $data['category'] ?: 'tecnologia',
                'status' => 'draft'
            ]);
            $result = createPost($draftData);
        }
        
        return $result;
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error en auto-guardado: ' . $e->getMessage()];
    }
}

/**
 * Obtener estadísticas del dashboard
 */
function getDashboardStats() {
    $db = getBlogDB();
    
    $stats = [];
    
    // Posts publicados
    $stmt = $db->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
    $stmt->execute();
    $stats['published_posts'] = $stmt->fetchColumn();
    
    // Borradores
    $stmt = $db->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'");
    $stmt->execute();
    $stats['draft_posts'] = $stmt->fetchColumn();
    
    // Total de vistas
    $stats['total_views'] = getTotalViews();
    
    // Suscriptores activos
    $stats['active_subscribers'] = getTotalSubscribers();
    
    // Posts más vistos (top 5)
    $stmt = $db->prepare("SELECT title, views, slug FROM blog_posts WHERE status = 'published' ORDER BY views DESC LIMIT 5");
    $stmt->execute();
    $stats['top_posts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Categorías más populares
    $stmt = $db->prepare("SELECT category, COUNT(*) as count FROM blog_posts WHERE status = 'published' GROUP BY category ORDER BY count DESC");
    $stmt->execute();
    $stats['popular_categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $stats;
}

/**
 * Validar permisos de administrador (básico)
 */
function validateAdminAccess() {
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
        return false;
    }
    return true;
}

/**
 * Limpiar y sanitizar contenido HTML
 */
function sanitizeHtmlContent($content) {
    // Lista de tags permitidos para el contenido del blog
    $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>';
    
    // Limpiar HTML
    $content = strip_tags($content, $allowedTags);
    
    // Sanitizar atributos de enlaces e imágenes
    $content = preg_replace_callback('/<a\s+([^>]*)>/i', function($matches) {
        $attrs = $matches[1];
        if (preg_match('/href\s*=\s*["\']([^"\']*)["\']/', $attrs, $hrefMatch)) {
            $href = filter_var($hrefMatch[1], FILTER_SANITIZE_URL);
            return '<a href="' . $href . '" target="_blank" rel="noopener">';
        }
        return '<a>';
    }, $content);
    
    $content = preg_replace_callback('/<img\s+([^>]*)>/i', function($matches) {
        $attrs = $matches[1];
        if (preg_match('/src\s*=\s*["\']([^"\']*)["\']/', $attrs, $srcMatch)) {
            $src = filter_var($srcMatch[1], FILTER_SANITIZE_URL);
            $alt = '';
            if (preg_match('/alt\s*=\s*["\']([^"\']*)["\']/', $attrs, $altMatch)) {
                $alt = htmlspecialchars($altMatch[1]);
            }
            return '<img src="' . $src . '" alt="' . $alt . '" style="max-width: 100%; height: auto;">';
        }
        return '';
    }, $content);
    
    return $content;
}

/**
 * Generar sitemap XML para el blog
 */
function generateBlogSitemap() {
    $db = getBlogDB();
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Página principal del blog
    $sitemap .= '<url>' . "\n";
    $sitemap .= '<loc>' . BLOG_URL . '</loc>' . "\n";
    $sitemap .= '<changefreq>daily</changefreq>' . "\n";
    $sitemap .= '<priority>1.0</priority>' . "\n";
    $sitemap .= '</url>' . "\n";
    
    // Posts publicados
    $stmt = $db->prepare("SELECT slug, updated_at FROM blog_posts WHERE status = 'published' ORDER BY updated_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($posts as $post) {
        $sitemap .= '<url>' . "\n";
        $sitemap .= '<loc>' . BLOG_URL . 'post.php?slug=' . $post['slug'] . '</loc>' . "\n";
        $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod>' . "\n";
        $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '<priority>0.8</priority>' . "\n";
        $sitemap .= '</url>' . "\n";
    }
    
    $sitemap .= '</urlset>';
    
    // Guardar sitemap
    file_put_contents(__DIR__ . '/../sitemap.xml', $sitemap);
    
    return true;
}
?>
