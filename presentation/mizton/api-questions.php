<?php
/**
 * API de Preguntas - Presentación Mizton
 * CRUD para preguntas de invitados durante la presentación
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Conexión a BD
require_once __DIR__ . '/../../bootstrap-landing.php';

// Iniciar sesión para identificar al invitado
session_start();

// Generar o recuperar guest_id de la sesión
if (!isset($_SESSION['presentation_guest_id'])) {
    $_SESSION['presentation_guest_id'] = 'guest_' . bin2hex(random_bytes(8));
}
$guestId = $_SESSION['presentation_guest_id'];

// Crear tabla si no existe
function ensureTable($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `presentation_questions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `guest_id` varchar(50) NOT NULL COMMENT 'ID de sesión del invitado',
            `sponsor_id` int(11) DEFAULT NULL COMMENT 'ID del patrocinador (futuro)',
            `question` text NOT NULL COMMENT 'Pregunta formulada',
            `whatsapp` varchar(30) DEFAULT NULL COMMENT 'WhatsApp del invitado (opcional)',
            `slide_number` int(11) DEFAULT NULL COMMENT 'Slide donde se formuló la pregunta',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` enum('pending','answered','archived') DEFAULT 'pending',
            PRIMARY KEY (`id`),
            KEY `idx_guest` (`guest_id`),
            KEY `idx_sponsor` (`sponsor_id`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

try {
    ensureTable($pdo);
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        // Obtener preguntas del invitado actual
        handleGetQuestions($pdo, $guestId);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($action === 'update_whatsapp') {
            handleUpdateWhatsapp($pdo, $guestId, $data);
        } else {
            handleCreateQuestion($pdo, $guestId, $data);
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        handleDeleteQuestion($pdo, $guestId, $data);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

/**
 * Obtener preguntas del invitado
 */
function handleGetQuestions($pdo, $guestId) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, question, whatsapp, slide_number, created_at, status
            FROM presentation_questions
            WHERE guest_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$guestId]);
        $questions = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'guest_id' => $guestId,
            'questions' => $questions
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al obtener preguntas']);
    }
}

/**
 * Crear nueva pregunta
 */
function handleCreateQuestion($pdo, $guestId, $data) {
    $question = trim($data['question'] ?? '');
    $whatsapp = trim($data['whatsapp'] ?? '');
    $slideNumber = intval($data['slide_number'] ?? 0);
    $sponsorId = $data['sponsor_id'] ?? null;
    
    if (empty($question)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'La pregunta no puede estar vacía']);
        return;
    }
    
    if (strlen($question) > 1000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'La pregunta es demasiado larga (máx 1000 caracteres)']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO presentation_questions (guest_id, sponsor_id, question, whatsapp, slide_number)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $guestId,
            $sponsorId ? intval($sponsorId) : null,
            $question,
            !empty($whatsapp) ? $whatsapp : null,
            $slideNumber
        ]);
        
        $newId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pregunta registrada',
            'question' => [
                'id' => $newId,
                'question' => $question,
                'whatsapp' => $whatsapp ?: null,
                'slide_number' => $slideNumber,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Create question error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al guardar la pregunta']);
    }
}

/**
 * Eliminar pregunta del invitado
 */
function handleDeleteQuestion($pdo, $guestId, $data) {
    $questionId = intval($data['id'] ?? 0);
    
    if (!$questionId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de pregunta requerido']);
        return;
    }
    
    try {
        // Solo puede borrar sus propias preguntas
        $stmt = $pdo->prepare("
            DELETE FROM presentation_questions
            WHERE id = ? AND guest_id = ?
        ");
        $stmt->execute([$questionId, $guestId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Pregunta eliminada']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pregunta no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    }
}

/**
 * Actualizar WhatsApp del invitado (para todas sus preguntas)
 */
function handleUpdateWhatsapp($pdo, $guestId, $data) {
    $whatsapp = trim($data['whatsapp'] ?? '');
    
    try {
        $stmt = $pdo->prepare("
            UPDATE presentation_questions
            SET whatsapp = ?
            WHERE guest_id = ?
        ");
        $stmt->execute([$whatsapp ?: null, $guestId]);
        
        echo json_encode(['success' => true, 'message' => 'WhatsApp actualizado']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar WhatsApp']);
    }
}
