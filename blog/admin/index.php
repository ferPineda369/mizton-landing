<?php
/**
 * Panel de Administración del Blog Mizton
 * Interfaz simple para crear y gestionar posts
 */

session_start();

// Autenticación básica (mejorar en producción)
$admin_password = 'mizton2024blog'; // Cambiar en producción

if (!isset($_SESSION['blog_admin']) && (!isset($_POST['password']) || $_POST['password'] !== $admin_password)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Mizton Blog</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
            .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .login-title { text-align: center; color: #1B4332; margin-bottom: 2rem; }
            .form-group { margin-bottom: 1rem; }
            label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
            input { width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 8px; font-size: 1rem; }
            button { width: 100%; background: #40916C; color: white; border: none; padding: 0.75rem; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; }
            button:hover { background: #1B4332; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2 class="login-title">Admin - Mizton Blog</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Acceder</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
    $_SESSION['blog_admin'] = true;
}

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_post':
            $result = createPost($_POST);
            $message = $result['success'] ? 'Post creado exitosamente' : 'Error: ' . $result['message'];
            break;
        case 'update_post':
            $result = updatePost($_POST['post_id'], $_POST);
            $message = $result['success'] ? 'Post actualizado exitosamente' : 'Error: ' . $result['message'];
            break;
        case 'delete_post':
            $result = deletePost($_POST['post_id']);
            $message = $result['success'] ? 'Post eliminado exitosamente' : 'Error: ' . $result['message'];
            break;
    }
}

// Obtener posts para la lista
$posts = getBlogPosts(50, 0, null, 'all'); // Incluir drafts
$categories = getCategoriesWithCount();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Mizton Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../../logo.gif" alt="Mizton" class="admin-logo">
                <h2>Blog Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="#posts" class="nav-item" onclick="showSection('posts')">
                    <i class="fas fa-file-alt"></i>
                    Posts
                </a>
                <a href="#new-post" class="nav-item" onclick="showSection('new-post')">
                    <i class="fas fa-plus"></i>
                    Nuevo Post
                </a>
                <a href="#newsletter" class="nav-item" onclick="showSection('newsletter')">
                    <i class="fas fa-envelope"></i>
                    Newsletter
                </a>
                <a href="../" class="nav-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Ver Blog
                </a>
                <a href="?logout=1" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1 id="page-title">Dashboard</h1>
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $result['success'] ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
            </header>

            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo getTotalPosts('published'); ?></h3>
                            <p>Posts Publicados</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo getTotalPosts('draft'); ?></h3>
                            <p>Borradores</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo getTotalViews(); ?></h3>
                            <p>Total Vistas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo getTotalSubscribers(); ?></h3>
                            <p>Suscriptores</p>
                        </div>
                    </div>
                </div>

                <div class="recent-posts">
                    <h3>Posts Recientes</h3>
                    <div class="posts-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Vistas</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($posts, 0, 5) as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                        <?php if ($post['featured']): ?>
                                        <span class="badge featured">Destacado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $post['category']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $post['status']; ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($post['views']); ?></td>
                                    <td><?php echo formatDate($post['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Posts Section -->
            <section id="posts" class="admin-section">
                <div class="section-header">
                    <h3>Gestionar Posts</h3>
                    <button class="btn btn-primary" onclick="showSection('new-post')">
                        <i class="fas fa-plus"></i>
                        Nuevo Post
                    </button>
                </div>

                <div class="posts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Vistas</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <?php if ($post['featured']): ?>
                                    <span class="badge featured">Destacado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $post['category']; ?></td>
                                <td>
                                    <span class="badge <?php echo $post['status']; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($post['views']); ?></td>
                                <td><?php echo formatDate($post['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-secondary" onclick="editPost(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deletePost(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- New Post Section -->
            <section id="new-post" class="admin-section">
                <div class="section-header">
                    <h3>Crear Nuevo Post</h3>
                </div>

                <form method="POST" class="post-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_post">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Título *</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Categoría *</label>
                            <select id="category" name="category" required>
                                <?php foreach ($categories as $key => $cat): ?>
                                <option value="<?php echo $key; ?>"><?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Extracto</label>
                        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Breve descripción del post..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Contenido *</label>
                        <textarea id="content" name="content" rows="15" required placeholder="Escribe el contenido del post en HTML..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="image">URL de Imagen</label>
                            <input type="url" id="image" name="image" placeholder="https://ejemplo.com/imagen.jpg">
                        </div>
                        <div class="form-group">
                            <label for="tags">Tags (separados por comas)</label>
                            <input type="text" id="tags" name="tags" placeholder="blockchain, fintech, tecnología">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select id="status" name="status">
                                <option value="draft">Borrador</option>
                                <option value="published">Publicado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="featured">
                                <input type="checkbox" id="featured" name="featured" value="1">
                                Post Destacado
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Crear Post
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i>
                            Limpiar
                        </button>
                    </div>
                </form>
            </section>

            <!-- Newsletter Section -->
            <section id="newsletter" class="admin-section">
                <div class="section-header">
                    <h3>Suscriptores del Newsletter</h3>
                </div>

                <div class="newsletter-stats">
                    <div class="stat-card">
                        <h4><?php echo getTotalSubscribers(); ?></h4>
                        <p>Total Suscriptores</p>
                    </div>
                </div>

                <div class="subscribers-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Fecha de Suscripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (getNewsletterSubscribers() as $subscriber): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $subscriber['status']; ?>">
                                        <?php echo ucfirst($subscriber['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($subscriber['subscribed_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="admin-scripts.js"></script>
</body>
</html>

<?php
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
