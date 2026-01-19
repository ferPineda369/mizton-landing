<?php
/**
 * Landing Page Interna para Proyectos sin Sitio Web Propio
 * Sistema modular de secciones dinámicas
 */

require_once __DIR__ . '/config/marketplace-config.php';
require_once __DIR__ . '/config/project-types-config.php';
require_once __DIR__ . '/includes/marketplace-functions.php';
require_once __DIR__ . '/includes/project-metadata-functions.php';

// Detectar si el usuario está autenticado
$isUserLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? null;

// Obtener código del proyecto
$projectCode = $_GET['code'] ?? '';

if (empty($projectCode)) {
    header('Location: /marketplace/');
    exit;
}

// Obtener proyecto completo
$project = getCompleteProject($projectCode);

if (!$project) {
    header('Location: /marketplace/');
    exit;
}

// Verificar que use landing interna
if (!$project['has_internal_landing']) {
    // Redirigir a la página normal del proyecto
    header('Location: /marketplace/project.php?code=' . $projectCode);
    exit;
}

// Obtener configuración del tipo de proyecto
$projectTypeConfig = getProjectTypeConfig($project['project_type']);

// Preparar metadata como array asociativo simple
$metadata = [];
foreach ($project['metadata'] as $key => $data) {
    $metadata[$key] = $data['value'];
}

$pageTitle = $project['name'] . ' - Marketplace Mizton';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($project['short_description']); ?>">
    <meta name="keywords" content="tokenización, RWA, <?php echo htmlspecialchars($project['category']); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($project['name']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($project['short_description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($project['main_image_url']); ?>">
    <meta property="og:type" content="website">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/marketplace/assets/css/marketplace.css">
    <link rel="stylesheet" href="/marketplace/assets/css/project-landing.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navigation -->
    <?php include __DIR__ . '/includes/navigation.php'; ?>

    <!-- Main Content -->
    <main class="project-landing">
        
        <?php
        // Renderizar secciones dinámicamente
        if (!empty($project['sections'])) {
            foreach ($project['sections'] as $section) {
                $sectionType = $section['section_type'];
                $sectionFile = __DIR__ . '/includes/sections/' . $sectionType . '.php';
                
                if (file_exists($sectionFile)) {
                    include $sectionFile;
                } else {
                    // Sección genérica si no existe template específico
                    include __DIR__ . '/includes/sections/generic.php';
                }
            }
        } else {
            // Si no hay secciones, mostrar mensaje
            echo '<div class="container" style="padding: 60px 20px; text-align: center;">';
            echo '<h2>Proyecto en Construcción</h2>';
            echo '<p>El contenido de este proyecto está siendo preparado.</p>';
            echo '</div>';
        }
        ?>

    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/marketplace/assets/js/project-landing.js"></script>
    
</body>
</html>
