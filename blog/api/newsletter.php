<?php
/**
 * API para suscripción al newsletter del blog
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/blog-config.php';
require_once '../includes/blog-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email requerido']);
    exit;
}

$email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Suscribir al newsletter
$result = subscribeNewsletter($email);

if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '¡Suscripción exitosa! Gracias por unirte a nuestro newsletter.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la suscripción. Inténtalo de nuevo.'
    ]);
}
?>
