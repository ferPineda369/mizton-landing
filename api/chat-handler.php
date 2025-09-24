<?php
/**
 * API Handler para Chat Automatizado con N8N
 * Endpoint: /landing/api/chat-handler.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../database.php';
require_once '../config/ai-config.php';

// Crear tablas necesarias
createAILogsTable();
createEscalationLogsTable();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'validate_referral':
            handleValidateReferral($input);
            break;
            
        case 'save_lead':
            handleSaveLead($input);
            break;
            
        case 'update_conversation':
            handleUpdateConversation($input);
            break;
            
        case 'get_faq_response':
            handleFAQResponse($input);
            break;
            
        case 'get_ai_response':
            handleAIResponse($input);
            break;
            
        case 'escalate_to_human':
            handleEscalateToHuman($input);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en chat-handler.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}

/**
 * Validar código de referido y verificar landing_preference
 */
function handleValidateReferral($input) {
    global $pdo;
    
    $referralCode = trim($input['referral_code'] ?? '');
    
    if (empty($referralCode)) {
        echo json_encode([
            'success' => true,
            'data' => [
                'valid' => false,
                'use_chat' => true,
                'referrer_name' => null
            ]
        ]);
        return;
    }
    
    // Validar código y obtener preferencia
    $validation = validateReferralCode($referralCode);
    
    if (!$validation['valid']) {
        echo json_encode([
            'success' => true,
            'data' => [
                'valid' => false,
                'use_chat' => true,
                'referrer_name' => null
            ]
        ]);
        return;
    }
    
    $landingPreference = $validation['user']['landing_preference'] ?? 0;
    $referrerName = $validation['user']['nameUser'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'valid' => true,
            'use_chat' => ($landingPreference == 0),
            'referrer_name' => $referrerName,
            'referrer_id' => $validation['user']['idUser']
        ]
    ]);
}

/**
 * Guardar nuevo lead del chat
 */
function handleSaveLead($input) {
    global $pdo;
    
    $email = trim($input['email'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    $sessionId = trim($input['session_id'] ?? '');
    $referrerId = $input['referrer_id'] ?? null;
    
    if (empty($email) || empty($sessionId)) {
        throw new Exception('Email y session_id son requeridos');
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }
    
    // Verificar si ya existe el lead
    $stmt = $pdo->prepare("SELECT id FROM chat_leads WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    
    if ($stmt->fetch()) {
        // Actualizar email si ya existe
        $stmt = $pdo->prepare("UPDATE chat_leads SET email = ?, status = 'email_captured', updated_at = NOW() WHERE session_id = ?");
        $stmt->execute([$email, $sessionId]);
    } else {
        // Crear nuevo lead
        $stmt = $pdo->prepare("
            INSERT INTO chat_leads (email, referral_code, referrer_id, session_id, status) 
            VALUES (?, ?, ?, ?, 'email_captured')
        ");
        $stmt->execute([$email, $referralCode, $referrerId, $sessionId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Lead guardado correctamente',
        'data' => ['email' => $email]
    ]);
}

/**
 * Actualizar conversación del chat
 */
function handleUpdateConversation($input) {
    global $pdo;
    
    $sessionId = trim($input['session_id'] ?? '');
    $message = trim($input['message'] ?? '');
    $sender = trim($input['sender'] ?? 'user'); // 'user' o 'bot'
    
    if (empty($sessionId) || empty($message)) {
        throw new Exception('session_id y message son requeridos');
    }
    
    // Obtener conversación actual
    $stmt = $pdo->prepare("SELECT conversation_data FROM chat_leads WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $result = $stmt->fetch();
    
    $conversation = [];
    if ($result && $result['conversation_data']) {
        $conversation = json_decode($result['conversation_data'], true) ?? [];
    }
    
    // Agregar nuevo mensaje
    $conversation[] = [
        'sender' => $sender,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Actualizar en BD
    $stmt = $pdo->prepare("
        UPDATE chat_leads 
        SET conversation_data = ?, updated_at = NOW() 
        WHERE session_id = ?
    ");
    $stmt->execute([json_encode($conversation), $sessionId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversación actualizada'
    ]);
}

/**
 * Obtener respuesta FAQ automatizada
 */
function handleFAQResponse($input) {
    $message = strtolower(trim($input['message'] ?? ''));
    
    // FAQ básicas de Mizton
    $faqs = [
        'hola' => '¡Hola! 👋 Bienvenido a Mizton. Soy tu asistente virtual y estoy aquí para ayudarte con cualquier pregunta sobre nuestra plataforma.',
        
        'que es mizton' => 'Mizton es una plataforma innovadora que ofrece membresías garantizadas con recuperación del 100% más ganancias adicionales.',
        
        'como funciona' => 'Nuestro sistema funciona así: 1) Te registras, 2) Aquieres un paquete de participación (Membresía), 3) Accedes a los dividendos globales de Mizton, 4) Al final del período si decides no continuar, recuperas el 100% de tu inversión inicial + el incentivo de al menos un 15%. ¡Es así de simple!',
        
        'qué recibo con la membresía' => 'Recibes un paquete de Tokens Corporativos que te dan acceso a los dividendos globales de Mizton. ¿Te gustaría conocer los detalles?',
        
        'cuanto puedo ganar' => 'Las ganancias varían según la cantidad de Tokens que poseas. Recuerda que hablamos de ganancias globales, más bonos adicionales. ¿Te interesa conocer los detalles específicos?',
        
        'es seguro' => 'Absolutamente. Mizton garantiza la recuperación del 100% de tu inversión inicial. Además, contamos con un sistema de respaldo sólido y transparente. Tu seguridad financiera es nuestra prioridad.',
        
        'como empezar' => 'Para empezar es muy fácil: 1) Regístrate en nuestra plataforma, 2) Obtén tu primera membresía, 3) ¡Comienza a generar ganancias!. ¿Te ayudo con el registro?',
        
        'registro' => 'El proceso de registro es simple y seguro. Solo necesitas tu email y haber sido invitado por uno de nuestros Miembros. Una vez registrado, podrás acceder a tu panel personal y adquirir tu membresía. ¿Quieres que te ayude a registrarte?',
        
        'contacto' => 'Puedes contactarnos de varias formas: a través de este chat, por WhatsApp, o por email. Nuestro equipo está disponible para resolver todas tus dudas. ¿Prefieres que te conecte con un asesor humano?',
        
        'hablar con humano' => 'Por supuesto! Te voy a conectar con uno de nuestros asesores especializados. Por favor espera un momento mientras te redirijo...',
        'asesor humano' => 'Perfecto! Te conectaré con un asesor humano especializado. Un momento por favor...',
        'hablar con alguien' => '¡Claro! Te voy a conectar con uno de nuestros asesores. Ellos podrán resolver todas tus dudas específicas.',
        'quiero hablar con una persona' => 'Entendido! Te conectaré con un asesor humano especializado en Mizton. Un momento por favor...',
        
        'precio' => 'Desde un paquete de $50 usd ya estás participando de los dividendos globales de Mizton. ¿Te gustaría adquirir más paquetes para obtener más ganancias?'
    ];
    
    // Buscar respuesta
    $response = null;
    $requiresHuman = false;
    
    foreach ($faqs as $keyword => $answer) {
        if (strpos($message, $keyword) !== false) {
            $response = $answer;
            
            // Detectar si solicita escalamiento a humano
            if (in_array($keyword, ['hablar con humano', 'asesor humano', 'hablar con alguien', 'quiero hablar con una persona'])) {
                $requiresHuman = true;
            }
            break;
        }
    }
    
    // Respuesta por defecto
    if (!$response) {
        $response = 'Entiendo tu pregunta. Para brindarte la información más precisa y personalizada, ¿te gustaría que te conecte con uno de nuestros asesores especializados? Ellos podrán resolver todas tus dudas específicas sobre Mizton.';
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'response' => $response,
            'requires_human' => $requiresHuman
        ]
    ]);
}

/**
 * Obtener respuesta de IA (FASE 2)
 */
function handleAIResponse($input) {
    $aiEnabled = ($_ENV['AI_ENABLED'] ?? 'false') === 'true';
    
    if (!$aiEnabled) {
        // Fallback a FAQ si IA no está habilitada
        handleFAQResponse($input);
        return;
    }
    
    require_once 'ai-handler.php';
    
    $message = trim($input['message'] ?? '');
    $sessionId = trim($input['session_id'] ?? '');
    
    if (empty($message) || empty($sessionId)) {
        throw new Exception('message y session_id son requeridos');
    }
    
    $aiResponse = getAIResponse($message, $sessionId);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'response' => $aiResponse,
            'powered_by' => 'ai',
            'requires_human' => false
        ]
    ]);
}

/**
 * Manejar escalamiento a asesor humano
 */
function handleEscalateToHuman($input) {
    global $pdo;
    
    $sessionId = trim($input['session_id'] ?? '');
    $email = trim($input['email'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    
    if (empty($sessionId)) {
        throw new Exception('session_id es requerido');
    }
    
    try {
        // Obtener información del lead
        $stmt = $pdo->prepare("SELECT * FROM chat_leads WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $lead = $stmt->fetch();
        
        if (!$lead) {
            throw new Exception('Sesión no encontrada');
        }
        
        // Obtener información del referidor si existe
        $referrerInfo = null;
        if ($lead['referrer_id']) {
            $stmt = $pdo->prepare("
                SELECT nameUser, emailUser, celularUser, countryUser, waUser, landing_preference 
                FROM tbluser 
                WHERE idUser = ?
            ");
            $stmt->execute([$lead['referrer_id']]);
            $referrerInfo = $stmt->fetch();
        }
        
        // Determinar método de contacto
        $contactMethod = determineHumanContactMethod($referrerInfo);
        
        // Actualizar estado del lead
        $stmt = $pdo->prepare("UPDATE chat_leads SET status = 'escalated_to_human', updated_at = NOW() WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        
        // Registrar escalamiento
        logEscalation($sessionId, $lead['email'], $contactMethod);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'escalated' => true,
                'contact_method' => $contactMethod['type'],
                'contact_info' => $contactMethod['info'],
                'message' => $contactMethod['message']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error en escalamiento: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Error al conectar con asesor. Intenta más tarde.',
            'data' => null
        ]);
    }
}

/**
 * Determinar método de contacto humano
 */
function determineHumanContactMethod($referrerInfo) {
    // Si hay referidor con atención personal
    if ($referrerInfo && $referrerInfo['landing_preference'] == 1 && $referrerInfo['waUser'] == 1) {
        require_once '../database.php';
        $whatsappNumber = buildWhatsAppNumber($referrerInfo['countryUser'], $referrerInfo['celularUser']);
        
        return [
            'type' => 'whatsapp_personal',
            'info' => $whatsappNumber,
            'message' => "Te conectaré con {$referrerInfo['nameUser']}, tu asesor personal de Mizton.",
            'referrer_name' => $referrerInfo['nameUser']
        ];
    }
    
    // Contacto por defecto (WhatsApp oficial)
    $defaultWhatsapp = $_ENV['DEFAULT_WHATSAPP'] ?? '5212226536090';
    
    return [
        'type' => 'whatsapp_default',
        'info' => $defaultWhatsapp,
        'message' => 'Te conectaré con nuestro equipo de asesores especializados de Mizton.',
        'referrer_name' => null
    ];
}

/**
 * Registrar escalamiento para seguimiento
 */
function logEscalation($sessionId, $email, $contactMethod) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO escalation_logs (session_id, email, contact_method, contact_info, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $sessionId,
            $email,
            $contactMethod['type'],
            json_encode($contactMethod)
        ]);
    } catch (Exception $e) {
        error_log("Error logging escalation: " . $e->getMessage());
    }
}
?>
