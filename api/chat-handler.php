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
 * Guardar lead en base de datos o recuperar existente
 */
function handleSaveLead($input) {
    global $pdo;
    
    $email = trim($input['email'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    $sessionId = trim($input['session_id'] ?? '');
    
    if (empty($email) || empty($sessionId)) {
        throw new Exception('email y session_id son requeridos');
    }
    
    try {
        // Verificar si ya existe un lead con este email
        $stmt = $pdo->prepare("SELECT * FROM chat_leads WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        $existingLead = $stmt->fetch();
        
        if ($existingLead) {
            // Email ya existe - actualizar session_id y devolver historial
            $stmt = $pdo->prepare("
                UPDATE chat_leads 
                SET session_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE email = ?
            ");
            $stmt->execute([$sessionId, $email]);
            
            // Devolver información del lead existente con historial
            $conversationData = json_decode($existingLead['conversation_data'], true) ?? [];
            
            echo json_encode([
                'success' => true,
                'message' => 'Bienvenido de vuelta! Continuemos donde lo dejamos.',
                'existing_user' => true,
                'data' => [
                    'lead_id' => $existingLead['id'],
                    'email' => $existingLead['email'],
                    'referral_code' => $existingLead['referral_code'],
                    'conversation_history' => $conversationData,
                    'status' => $existingLead['status']
                ]
            ]);
            return;
        }
        
        // Email nuevo - crear nuevo lead
        $referrerId = null;
        if (!empty($referralCode)) {
            $stmt = $pdo->prepare("SELECT idUser FROM tbluser WHERE codigoUser = ?");
            $stmt->execute([$referralCode]);
            $referrer = $stmt->fetch();
            if ($referrer) {
                $referrerId = $referrer['idUser'];
            }
        }
        
        // Insertar nuevo lead
        $stmt = $pdo->prepare("
            INSERT INTO chat_leads (email, referral_code, referrer_id, session_id, status, conversation_data) 
            VALUES (?, ?, ?, ?, 'email_captured', '[]')
        ");
        
        $stmt->execute([$email, $referralCode, $referrerId, $sessionId]);
        $leadId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfecto! Ahora puedo ayudarte con cualquier pregunta sobre Mizton.',
            'existing_user' => false,
            'data' => [
                'lead_id' => $leadId,
                'email' => $email,
                'referral_code' => $referralCode
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error guardando lead: " . $e->getMessage());
        throw new Exception('Error guardando información');
    }
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
    
    // FAQ básicas de Mizton - Más conversacionales y con más variaciones
    $faqs = [
        // Saludos
        'hola' => '¡Hola! 👋 Bienvenido a Mizton. Soy tu asistente virtual y estoy aquí para ayudarte con cualquier pregunta sobre nuestra plataforma.',
        'buenos dias' => '¡Buenos días! 🌅 ¡Qué gusto tenerte aquí! Soy el asistente de Mizton y estoy listo para resolver todas tus dudas.',
        'buenas tardes' => '¡Buenas tardes! ☀️ Perfecto momento para conocer sobre Mizton. ¿En qué puedo ayudarte hoy?',
        'buenas noches' => '¡Buenas noches! 🌙 Aunque sea tarde, estoy aquí 24/7 para ayudarte con Mizton. ¿Qué te gustaría saber?',
        
        // Qué es Mizton
        'que es mizton' => 'Mizton es una plataforma innovadora que ofrece membresías garantizadas con recuperación del 100% más ganancias adicionales. ¡Es como tener lo mejor de ambos mundos! 🚀',
        'mizton' => 'Mizton es tu oportunidad de participar en dividendos globales con total seguridad. ¿Te gustaría saber cómo funciona exactamente?',
        'plataforma' => 'Nuestra plataforma está diseñada para que obtengas ganancias de forma segura y transparente. ¿Hay algo específico que te gustaría conocer?',
        
        // Funcionamiento
        'como funciona' => 'Nuestro sistema funciona así: 1) Te registras, 2) Adquieres un paquete de participación (Membresía), 3) Accedes a los dividendos globales de Mizton, 4) Al final del período si decides no continuar, recuperas el 100% de tu inversión inicial + el incentivo de al menos un 15%. ¡Es así de simple! 🎯',
        'funciona' => '¡Es súper sencillo! Básicamente inviertes, generas ganancias mensuales y al final recuperas todo tu dinero más ganancias. ¿Te explico paso a paso?',
        'sistema' => 'Nuestro sistema está basado en dividendos globales. Tú participas con tokens corporativos y recibes tu parte proporcional. ¿Quieres más detalles?',
        
        // Membresías y tokens
        'membresia' => 'Las membresías son tu entrada a los dividendos globales de Mizton. Cada membresía incluye tokens corporativos que generan ganancias. ¿Te interesa saber los precios?',
        'tokens' => 'Los tokens corporativos son tu participación en Mizton. Mientras más tokens tengas, mayor será tu parte de los dividendos globales. ¡Es proporcional! 📈',
        'que recibo' => 'Recibes un paquete de Tokens Corporativos que te dan acceso a los dividendos globales de Mizton. ¿Te gustaría conocer los detalles específicos?',
        
        // Ganancias
        'cuanto puedo ganar' => 'Las ganancias varían según la cantidad de Tokens que poseas. Recuerda que hablamos de ganancias globales, más bonos adicionales por referidos. ¿Te interesa conocer los detalles específicos? 💰',
        'ganancias' => '¡Las ganancias son lo emocionante! Participas de dividendos globales más bonos por referir personas. ¿Quieres que te explique cómo se calculan?',
        'dinero' => 'Con Mizton puedes generar ingresos de dos formas: dividendos por tus tokens y bonos por referir personas. ¿Te gustaría saber más sobre alguna?',
        'ingresos' => 'Los ingresos en Mizton provienen de los dividendos globales que se reparten entre todos los miembros según sus tokens. ¡Es transparente y justo! ⚖️',
        
        // Seguridad
        'es seguro' => 'Absolutamente. Mizton garantiza la recuperación del 100% de tu inversión inicial más un incentivo mínimo del 15%. Además, contamos con un sistema de respaldo sólido y transparente. Tu seguridad financiera es nuestra prioridad. 🛡️',
        'seguro' => '¡Totalmente seguro! Tienes garantía del 100% de recuperación más ganancias mínimas del 15%. ¿Te gustaría conocer más sobre nuestras garantías?',
        'confiable' => 'Mizton es completamente confiable. Tenemos sistemas de respaldo y transparencia total. ¿Hay algo específico sobre la seguridad que te preocupe?',
        'garantia' => 'Nuestra garantía es simple: recuperas el 100% de tu inversión inicial + mínimo 15% de incentivo. ¡Sin letra pequeña! 📋',
        
        // Registro y inicio
        'como empezar' => 'Para empezar es muy fácil: 1) Regístrate en nuestra plataforma, 2) Obtén tu primera membresía, 3) ¡Comienza a generar ganancias! ¿Te ayudo con el proceso de registro? 🚀',
        'empezar' => '¡Perfecto que quieras empezar! El proceso es súper simple. ¿Prefieres que te guíe paso a paso o que te conecte directamente con un asesor?',
        'registro' => 'El proceso de registro es simple y seguro. Solo necesitas tu email y haber sido invitado por uno de nuestros Miembros. Una vez registrado, podrás acceder a tu panel personal y adquirir tu membresía. ¿Quieres que te ayude a registrarte? 📝',
        'unirse' => '¡Excelente decisión! Para unirte solo necesitas registrarte con tu email. ¿Ya tienes el código de referido de quien te invitó?',
        
        // Precios
        'precio' => 'Desde un paquete de $50 USD ya estás participando de los dividendos globales de Mizton. ¿Te gustaría adquirir más paquetes para obtener más ganancias? 💵',
        'costo' => 'El costo mínimo es de $50 USD para tu primera membresía. ¡Es súper accesible! ¿Te interesa conocer los diferentes paquetes disponibles?',
        'cuanto cuesta' => 'Puedes empezar con solo $50 USD. Es una inversión muy accesible considerando que recuperas el 100% más ganancias. ¿Quieres ver las opciones?',
        
        // Contacto y escalamiento
        'contacto' => 'Puedes contactarnos de varias formas: a través de este chat, por WhatsApp, o por email. Nuestro equipo está disponible para resolver todas tus dudas. ¿Prefieres que te conecte con un asesor humano? 📞',
        'hablar con humano' => 'Por supuesto! Te voy a conectar con uno de nuestros asesores especializados. Por favor espera un momento mientras te redirijo... 👤',
        'asesor humano' => 'Perfecto! Te conectaré con un asesor humano especializado. Un momento por favor... 🤝',
        'hablar con alguien' => '¡Claro! Te voy a conectar con uno de nuestros asesores. Ellos podrán resolver todas tus dudas específicas. 💬',
        'quiero hablar con una persona' => 'Entendido! Te conectaré con un asesor humano especializado en Mizton. Un momento por favor... 🙋‍♂️',
        'persona real' => '¡Por supuesto! Nada como hablar con una persona real. Te conecto con un asesor especializado ahora mismo. ⏰'
    ];
    
    // Buscar respuesta
    $response = null;
    $requiresHuman = false;
    
    foreach ($faqs as $keyword => $answer) {
        if (strpos($message, $keyword) !== false) {
            $response = $answer;
            
            // Detectar si solicita escalamiento a humano
            if (in_array($keyword, ['hablar con humano', 'asesor humano', 'hablar con alguien', 'quiero hablar con una persona', 'persona real'])) {
                $requiresHuman = true;
            }
            break;
        }
    }
    
    // Respuestas por defecto más naturales y variadas
    if (!$response) {
        $defaultResponses = [
            'Interesante pregunta! 🤔 Mizton tiene muchos aspectos fascinantes. ¿Te gustaría que profundice en algún tema específico como las ganancias, la seguridad o el proceso de registro?',
            
            'Me encanta que preguntes eso! 😊 Mizton es realmente innovador. ¿Hay algo particular sobre nuestro sistema de membresías que te gustaría conocer mejor?',
            
            'Excelente consulta! 👍 Cada aspecto de Mizton está diseñado pensando en nuestros miembros. ¿Te interesa saber más sobre cómo funciona, los precios, o tal vez las garantías?',
            
            'Esa es una pregunta muy común! 💡 Muchos de nuestros miembros tenían la misma duda. ¿Te gustaría que te explique paso a paso cómo funciona Mizton?',
            
            'Perfecto que preguntes eso! 🎯 Es importante entender bien antes de tomar una decisión. ¿Prefieres que te conecte con un asesor especializado para una explicación personalizada?'
        ];
        
        // Seleccionar respuesta aleatoria
        $response = $defaultResponses[array_rand($defaultResponses)];
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
        $whatsappNumber = buildWhatsAppNumber($referrerInfo['countryUser'], $referrerInfo['celularUser']);
        
        return [
            'type' => 'whatsapp_personal',
            'info' => $whatsappNumber,
            'message' => "Te conectaré con {$referrerInfo['nameUser']}, tu asesor personal de Mizton.",
            'referrer_name' => $referrerInfo['nameUser']
        ];
    }
    
    // Contacto por defecto (WhatsApp oficial)
    $defaultWhatsapp = $_ENV['DEFAULT_WHATSAPP'] ?? '5212215695942';
    
    return [
        'type' => 'whatsapp_default',
        'info' => $defaultWhatsapp,
        'message' => 'Te conectaré con nuestro equipo de asesores especializados de Mizton.',
        'referrer_name' => null
    ];
}

/**
 * Construir número de WhatsApp completo
 */
function buildWhatsAppNumber($countryCode, $phoneNumber) {
    // Limpiar número de teléfono
    $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
    
    // Si ya tiene código de país, devolverlo
    if (strlen($cleanPhone) > 10) {
        return $cleanPhone;
    }
    
    // Agregar código de país por defecto (México)
    $defaultCountryCode = '52';
    if ($countryCode && is_numeric($countryCode)) {
        $defaultCountryCode = $countryCode;
    }
    
    return $defaultCountryCode . $cleanPhone;
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
