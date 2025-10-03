<?php
/**
 * Configuración del Blog Mizton
 * Configuración general y conexión a base de datos
 */

// Configuración de la base de datos (usando la misma del panel)
require_once '../../database.php';

// Configuración del blog
define('BLOG_TITLE', 'Mizton Blog');
define('BLOG_DESCRIPTION', 'Tecnología e Innovación Financiera');
define('BLOG_URL', 'https://mizton.cat/blog/');
define('POSTS_PER_PAGE', 6);
define('ADMIN_EMAIL', 'admin@mizton.cat');

// Categorías del blog
$blog_categories = [
    'blockchain' => [
        'name' => 'Blockchain',
        'description' => 'Tecnología blockchain y criptomonedas',
        'color' => '#1B4332'
    ],
    'fintech' => [
        'name' => 'Fintech',
        'description' => 'Tecnología financiera e innovación',
        'color' => '#40916C'
    ],
    'rwa' => [
        'name' => 'Tokenización RWA',
        'description' => 'Real World Assets y tokenización',
        'color' => '#52B788'
    ],
    'tecnologia' => [
        'name' => 'Tecnología',
        'description' => 'Tendencias tecnológicas generales',
        'color' => '#74C69D'
    ],
    'mizton' => [
        'name' => 'Mizton',
        'description' => 'Noticias y actualizaciones de Mizton',
        'color' => '#95D5B2'
    ]
];

// Configuración de imágenes
define('BLOG_IMAGES_PATH', 'assets/images/');
define('BLOG_IMAGES_URL', BLOG_URL . 'assets/images/');

// Configuración de SEO
$seo_config = [
    'default_image' => BLOG_IMAGES_URL . 'blog-default.jpg',
    'twitter_handle' => '@mizton_official',
    'facebook_app_id' => '684765634652448'
];

// Función para obtener la conexión a la base de datos
function getBlogDB() {
    global $conn;
    return $conn;
}

// Crear tabla de posts si no existe
function createBlogTables() {
    $db = getBlogDB();
    
    $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content LONGTEXT NOT NULL,
        image VARCHAR(255),
        category VARCHAR(50) NOT NULL,
        tags JSON,
        author VARCHAR(100) DEFAULT 'Mizton Team',
        status ENUM('draft', 'published') DEFAULT 'draft',
        featured BOOLEAN DEFAULT FALSE,
        read_time INT DEFAULT 5,
        views INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        published_at TIMESTAMP NULL
    )";
    
    $db->query($sql);
    
    // Tabla para newsletter
    $sql_newsletter = "CREATE TABLE IF NOT EXISTS blog_newsletter (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        status ENUM('active', 'unsubscribed') DEFAULT 'active',
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->query($sql_newsletter);
}

// Inicializar tablas
createBlogTables();
?>
