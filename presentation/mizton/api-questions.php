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

// Obtener código de referido del sponsor (si existe)
$sponsorRefCode = $_SESSION['sponsor_ref_code'] ?? null;
$sponsorId = null;

// Si hay código de referido, obtener el idUser correspondiente
if ($sponsorRefCode) {
    try {
        $stmt = $pdo->prepare("SELECT idUser FROM tbluser WHERE userUser = ? LIMIT 1");
        $stmt->execute([$sponsorRefCode]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $sponsorId = $user['idUser'];
        }
    } catch (PDOException $e) {
        error_log("Error getting sponsor ID: " . $e->getMessage());
    }
}

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

// Función para validar email
function validateEmail($email) {
    if (empty($email)) return ['valid' => true, 'value' => null];
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        return ['valid' => false, 'error' => 'Correo electrónico inválido'];
    }
    return ['valid' => true, 'value' => strtolower($email)];
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
            `sponsor_id` int(11) DEFAULT NULL COMMENT 'ID del usuario patrocinador',
            `question` text NOT NULL COMMENT 'Pregunta formulada',
            `email` varchar(100) DEFAULT NULL COMMENT 'Email del invitado para enviar respuestas',
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
        if ($action === 'update_email') {
            handleUpdateEmail($pdo, $guestId, $data);
        } elseif ($action === 'get_email_info') {
            handleGetEmailInfo($pdo, $guestId);
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
            SELECT id, question, email, slide_number, created_at, status
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
    global $sponsorId;
    
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
    $emailInput = sanitizeInput($data['email'] ?? '', 100);
    $slideNumber = filter_var($data['slide_number'] ?? 0, FILTER_VALIDATE_INT);
    if ($slideNumber === false) $slideNumber = 0;
    
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
    
    // Validar email si se proporciona
    $emailValidation = validateEmail($emailInput);
    if (!$emailValidation['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $emailValidation['error']]);
        return;
    }
    $email = $emailValidation['value'];
    
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
            INSERT INTO presentation_questions (guest_id, sponsor_id, question, email, slide_number, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$guestId, $sponsorId, $question, $email, $slideNumber, $clientIp]);
        
        $newId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pregunta registrada',
            'question' => [
                'id' => $newId,
                'question' => $question,
                'email' => $email,
                'slide_number' => $slideNumber,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Create question PDO error: " . $e->getMessage());
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
 * Actualizar email del invitado (para todas sus preguntas)
 */
function handleUpdateEmail($pdo, $guestId, $data) {
    $emailInput = sanitizeInput($data['email'] ?? '', 100);
    $emailValidation = validateEmail($emailInput);
    
    if (!$emailValidation['valid'] || empty($emailValidation['value'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $emailValidation['error'] ?? 'Email requerido']);
        return;
    }
    
    $email = $emailValidation['value'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE presentation_questions SET email = ? WHERE guest_id = ?
        ");
        $stmt->execute([$email, $guestId]);
        echo json_encode(['success' => true, 'message' => 'Email actualizado']);
    } catch (PDOException $e) {
        error_log("Update email error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar email']);
    }
}

/**
 * Obtener email guardado del invitado
 */
function handleGetEmailInfo($pdo, $guestId) {
    try {
        $stmt = $pdo->prepare("
            SELECT email FROM presentation_questions
            WHERE guest_id = ? AND email IS NOT NULL
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$guestId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $result ? ['email' => $result['email']] : null
        ]);
    } catch (PDOException $e) {
        error_log("Get email info error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al obtener email']);
    }
}
