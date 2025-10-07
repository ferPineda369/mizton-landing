<?php
// Redirección directa al directorio admin
// Este archivo evita que el rewrite interfiera con /news/admin

// Verificar si es una petición para admin
$requestUri = $_SERVER['REQUEST_URI'];

// Si es exactamente /news/admin o /news/admin/, redirigir al index del admin
if (preg_match('#^/news/admin/?$#', $requestUri)) {
    header('Location: /news/admin/index.php', true, 301);
    exit;
}

// Si es cualquier otra ruta de admin, servir directamente
if (strpos($requestUri, '/news/admin/') === 0) {
    // Extraer la ruta después de /news/admin/
    $adminPath = substr($requestUri, strlen('/news/admin/'));
    
    // Si está vacía, ir al index
    if (empty($adminPath) || $adminPath === '/') {
        include __DIR__ . '/admin/index.php';
        exit;
    }
    
    // Si es un archivo específico, incluirlo
    $filePath = __DIR__ . '/admin/' . $adminPath;
    if (file_exists($filePath) && is_file($filePath)) {
        include $filePath;
        exit;
    }
}

// Si llegamos aquí, mostrar 404
http_response_code(404);
include __DIR__ . '/404.php';
?>
