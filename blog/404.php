<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - Mizton Blog</title>
    <meta name="description" content="La página que buscas no existe en el blog de Mizton.">
    
    <!-- Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/blog-styles.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--light-gray) 0%, rgba(149, 213, 178, 0.1) 100%);
            padding: 2rem;
        }
        
        .error-content {
            text-align: center;
            max-width: 600px;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: var(--accent-green);
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        .error-description {
            font-size: 1.125rem;
            color: var(--text-medium);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .error-btn-primary {
            background: var(--accent-green);
            color: var(--white);
        }
        
        .error-btn-primary:hover {
            background: var(--primary-green);
            transform: translateY(-2px);
        }
        
        .error-btn-secondary {
            background: var(--white);
            color: var(--text-dark);
            border: 1px solid var(--medium-gray);
        }
        
        .error-btn-secondary:hover {
            background: var(--light-gray);
            transform: translateY(-2px);
        }
        
        .suggested-posts {
            margin-top: 3rem;
            text-align: left;
        }
        
        .suggested-posts h3 {
            color: var(--primary-green);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .suggested-list {
            display: grid;
            gap: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .suggested-item {
            background: var(--white);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-fast);
        }
        
        .suggested-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .suggested-item a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .suggested-item a:hover {
            color: var(--accent-green);
        }
        
        .suggested-meta {
            font-size: 0.875rem;
            color: var(--text-medium);
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .error-btn {
                width: 200px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Página no encontrada</h1>
            <p class="error-description">
                Lo sentimos, la página que buscas no existe o ha sido movida. 
                Pero no te preocupes, tenemos mucho contenido interesante para ti.
            </p>
            
            <div class="error-actions">
                <a href="index.php" class="error-btn error-btn-primary">
                    <i class="fas fa-home"></i>
                    Volver al Blog
                </a>
                <a href="../" class="error-btn error-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Ir a Mizton
                </a>
            </div>
            
            <?php
            // Mostrar posts sugeridos
            require_once 'config/blog-config.php';
            require_once 'includes/blog-functions.php';
            
            $suggestedPosts = getBlogPosts(3);
            if (!empty($suggestedPosts)):
            ?>
            <div class="suggested-posts">
                <h3>Artículos que podrían interesarte</h3>
                <div class="suggested-list">
                    <?php foreach ($suggestedPosts as $post): ?>
                    <div class="suggested-item">
                        <a href="post.php?slug=<?php echo $post['slug']; ?>">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                        <div class="suggested-meta">
                            <?php echo $post['category']; ?> • <?php echo formatDate($post['published_at']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Tracking de 404 para analytics
        if (typeof fbq !== 'undefined') {
            fbq('track', 'ViewContent', {
                content_name: '404 Error Page',
                content_category: 'Error',
                value: 0,
                currency: 'USD'
            });
        }
        
        // Redirección inteligente después de 10 segundos
        setTimeout(() => {
            if (confirm('¿Te gustaría ser redirigido al blog principal?')) {
                window.location.href = 'index.php';
            }
        }, 10000);
    </script>
</body>
</html>
