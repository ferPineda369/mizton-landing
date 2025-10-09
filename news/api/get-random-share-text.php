<?php
/**
 * API para obtener texto aleatorio de compartir para artículos del blog
 * Obtiene un texto aleatorio de tbl_tools_share_texts donde video_id=31
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configuración de base de datos
require_once '../config/blog-config.php';

try {
    // Consultar texto aleatorio para video_id=31
    $stmt = $pdo->prepare("
        SELECT share_text 
        FROM tbl_tools_share_texts 
        WHERE video_id = 31 AND is_active = 1 
        ORDER BY RAND() 
        LIMIT 1
    ");
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'text' => $result['share_text']
        ]);
    } else {
        // Fallback si no hay textos para video_id=31
        echo json_encode([
            'success' => false,
            'message' => 'No hay textos disponibles para compartir',
            'fallback_text' => 'Descubre más sobre tokenización y el futuro financiero en Mizton'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error en get-random-share-text.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor',
        'fallback_text' => 'Descubre más sobre tokenización y el futuro financiero en Mizton'
    ]);
}
?>
