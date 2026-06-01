<?php
/**
 * API de Preguntas - Presentación Mizton
 * CRUD para preguntas de invitados durante la presentación
 * Con medidas de seguridad aplicadas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

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
    $_SESSION['presentation_guest_id'] = 'guest_' . bin2hex(random_bytes(16));
}
$guestId = $_SESSION['presentation_guest_id'];

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Configuración de seguridad
const MAX_QUESTIONS_PER_HOUR = 30;
const MAX_QUESTIONS_PER_SESSION = 50;
const RATE_LIMIT_WINDOW = 3600; // 1 hora

// Obtener IP del cliente
function getClientIp() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

$clientIp = getClientIp();

// Función para sanitizar input
function sanitizeInput($input, $maxLength = 1000) {
    if (!is_string($input)) return '';
    $input = substr($input, 0, $maxLength);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($input);
}

// Función para validar WhatsApp
function validateWhatsApp($number) {
    if (empty($number)) return ['valid' => true, 'value' => null];
    // Solo permitir números, espacios, + y paréntesis
    $cleaned = preg_replace('/[^0-9+\s\(\)\-]/', '', $number);
    if (strlen($cleaned) < 8 || strlen($cleaned) > 20) {
        return ['valid' => false, 'error' => 'Número de WhatsApp inválido'];
    }
    return ['valid' => true, 'value' => $cleaned];
}

// Función para verificar rate limiting
function checkRateLimit($pdo, $guestId, $clientIp) {
    try {
        // Verificar preguntas en la última hora por guest_id
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM presentation_questions
            WHERE guest_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$guestId, RATE_LIMIT_WINDOW]);
        $countHour = $stmt->fetchColumn();
        
        if ($countHour >= MAX_QUESTIONS_PER_HOUR) {
            return ['allowed' => false, 'error' => 'Límite de preguntas por hora alcanzado. Intenta más tarde.'];
        }
        
        // Verificar total de preguntas por sesión
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM presentation_questions WHERE guest_id = ?
        ");
        $stmt->execute([$guestId]);
        $countTotal = $stmt->fetchColumn();
        
        if ($countTotal >= MAX_QUESTIONS_PER_SESSION) {
            return ['allowed' => false, 'error' => 'Límite total de preguntas alcanzado para esta sesión.'];
        }
        
        return ['allowed' => true];
    } catch (PDOException $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        return ['allowed' => false, 'error' => 'Error de verificación'];
    }
}

// Logging de actividad sospechosa
function logSecurityEvent($event, $details = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $GLOBALS['clientIp'] ?? 'unknown',
        'guest_id' => $GLOBALS['guestId'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    error_log("[SECURITY] " . json_encode($logEntry));
}

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
            `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP del cliente',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` enum('pending','answered','archived') DEFAULT 'pending',
            PRIMARY KEY (`id`),
            KEY `idx_guest` (`guest_id`),
            KEY `idx_sponsor` (`sponsor_id`),
            KEY `idx_created` (`created_at`),
            KEY `idx_ip` (`ip_address`)
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
            handleUpdateWhatsapp($pdo, $guestId, $data, $clientIp);
        } else {
            handleCreateQuestion($pdo, $guestId, $data, $clientIp);
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
 * Crear nueva pregunta - Con seguridad reforzada
 */
function handleCreateQuestion($pdo, $guestId, $data, $clientIp) {
    // Verificar rate limiting
    $rateCheck = checkRateLimit($pdo, $guestId, $clientIp);
    if (!$rateCheck['allowed']) {
        logSecurityEvent('rate_limit_exceeded', ['reason' => $rateCheck['error']]);
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => $rateCheck['error']]);
        return;
    }
    
    // Sanitizar inputs
    $question = sanitizeInput($data['question'] ?? '', 1000);
    $whatsappInput = sanitizeInput($data['whatsapp'] ?? '', 30);
    $slideNumber = filter_var($data['slide_number'] ?? 0, FILTER_VALIDATE_INT);
    if ($slideNumber === false) $slideNumber = 0;
    $sponsorId = isset($data['sponsor_id']) ? filter_var($data['sponsor_id'], FILTER_VALIDATE_INT) : null;
    
    // Validar pregunta
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
    
    // Validar WhatsApp
    $waValidation = validateWhatsApp($whatsappInput);
    if (!$waValidation['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $waValidation['error']]);
        return;
    }
    $whatsapp = $waValidation['value'];
    
    // Detectar contenido sospechoso (XSS básico)
    $suspiciousPatterns = ['/script/i', '/javascript/i', '/on\w+=/i', '/<iframe/i', '/<object/i'];
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $question)) {
            logSecurityEvent('suspicious_content_detected', ['question' => substr($question, 0, 100)]);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Contenido no permitido en la pregunta']);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO presentation_questions (guest_id, sponsor_id, question, whatsapp, slide_number, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $guestId,
            $sponsorId,
            $question,
            $whatsapp,
            $slideNumber,
            $clientIp
        ]);
        
        $newId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pregunta registrada',
            'question' => [
                'id' => $newId,
                'question' => $question,
                'whatsapp' => $whatsapp,
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
 * Eliminar pregunta del invitado - Con seguridad
 */
function handleDeleteQuestion($pdo, $guestId, $data) {
    $questionId = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!$questionId || $questionId < 1) {
        logSecurityEvent('invalid_delete_attempt', ['id' => $data['id'] ?? 'null']);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de pregunta inválido']);
        return;
    }
    
    try {
        // Verificar que la pregunta pertenece al guest_id antes de borrar
        $stmt = $pdo->prepare("
            SELECT id FROM presentation_questions WHERE id = ? AND guest_id = ?
        ");
        $stmt->execute([$questionId, $guestId]);
        
        if (!$stmt->fetch()) {
            logSecurityEvent('unauthorized_delete_attempt', ['question_id' => $questionId]);
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'No autorizado para eliminar esta pregunta']);
            return;
        }
        
        // Solo puede borrar preguntas recientes (menos de 24 horas)
        $stmt = $pdo->prepare("
            DELETE FROM presentation_questions
            WHERE id = ? AND guest_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$questionId, $guestId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Pregunta eliminada']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Solo puedes eliminar preguntas de las últimas 24 horas']);
        }
    } catch (PDOException $e) {
        error_log("Delete question error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al eliminar']);
    }
}

/**
 * Actualizar WhatsApp del invitado (para todas sus preguntas) - Con seguridad
 */
function handleUpdateWhatsapp($pdo, $guestId, $data, $clientIp) {
    $whatsappInput = sanitizeInput($data['whatsapp'] ?? '', 30);
    
    // Validar WhatsApp
    $waValidation = validateWhatsApp($whatsappInput);
    if (!$waValidation['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $waValidation['error']]);
        return;
    }
    
    $whatsapp = $waValidation['value'];
    
    // Limitar número de actualizaciones (máximo 5 por sesión)
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM presentation_questions 
            WHERE guest_id = ? AND whatsapp IS NOT NULL
        ");
        $stmt->execute([$guestId]);
        $hasPrevious = $stmt->fetchColumn() > 0;
        
        $stmt = $pdo->prepare("
            UPDATE presentation_questions
            SET whatsapp = ?
            WHERE guest_id = ?
        ");
        $stmt->execute([$whatsapp, $guestId]);
        
        echo json_encode(['success' => true, 'message' => 'WhatsApp actualizado']);
    } catch (PDOException $e) {
        error_log("Update WhatsApp error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar WhatsApp']);
    }
}
