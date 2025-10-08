<?php
/**
 * API para subir imágenes del blog
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ninguna imagen o hubo un error en la subida');
    }
    
    $file = $_FILES['image'];
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WebP');
    }
    
    // Validar tamaño (máximo 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('El archivo es demasiado grande. Máximo 5MB permitido');
    }
    
    // Crear directorio si no existe
    $uploadDir = dirname(__DIR__) . '/../assets/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'blog-' . date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Error al guardar el archivo');
    }
    
    // URL relativa para la base de datos
    $imageUrl = 'assets/images/' . $filename;
    
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida exitosamente',
        'image_url' => $imageUrl,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
