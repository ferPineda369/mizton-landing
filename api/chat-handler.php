<?php
/**
 * API Handler para Chat Automatizado con N8N
 * Endpoint: /landing/api/chat-handler.php
 */

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file_get_contents(__DIR__ . '/../.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

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
createChatLeadsTable();
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
            
        case 'verify_referral_code':
            handleVerifyReferralCode($input);
            break;
            
        case 'debug_ai_config':
            handleDebugAIConfig();
            break;
            
        case 'generate_embeddings':
            handleGenerateEmbeddings();
            break;
            
        case 'check_ai_costs':
            handleCheckAICosts();
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en chat-handler.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error técnico: ' . $e->getMessage(),
        'data' => null,
        'debug' => [
            'action' => $action ?? 'unknown',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
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
    
    error_log("=== GUARDANDO LEAD ===");
    error_log("Input: " . json_encode($input));
    
    $email = trim($input['email'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    $sessionId = trim($input['session_id'] ?? '');
    
    error_log("Email: $email, SessionId: $sessionId, ReferralCode: $referralCode");
    
    if (empty($email) || empty($sessionId)) {
        error_log("ERROR: Datos faltantes - email o session_id vacíos");
        throw new Exception('email y session_id son requeridos');
    }
    
    try {
        // Verificar que la tabla existe
        error_log("Verificando tabla chat_leads...");
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'chat_leads'");
        $stmt->execute();
        $tableExists = $stmt->fetch();
        
        if (!$tableExists) {
            error_log("ERROR: Tabla chat_leads no existe");
            throw new Exception('Tabla chat_leads no existe');
        }
        
        // Verificar si ya existe un lead con este email
        error_log("Buscando lead existente con email: $email");
        $stmt = $pdo->prepare("SELECT * FROM chat_leads WHERE email = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        $existingLead = $stmt->fetch();
        error_log("Lead existente encontrado: " . ($existingLead ? "SÍ" : "NO"));
        
        if ($existingLead) {
            error_log("Lead existente encontrado, actualizando session_id");
            // Email ya existe - actualizar session_id y devolver historial
            $stmt = $pdo->prepare("
                UPDATE chat_leads 
                SET session_id = ?, updated_at = NOW() 
                WHERE email = ?
            ");
            $stmt->execute([$sessionId, $email]);
            error_log("Session_id actualizado correctamente");
            
            // Devolver información del lead existente con historial
            $conversationData = [];
            if (!empty($existingLead['conversation_data'])) {
                $conversationData = json_decode($existingLead['conversation_data'], true) ?? [];
            }
            
            // Verificar si ya tiene referral_code
            $hasReferralCode = !empty($existingLead['referral_code']) && !empty($existingLead['referrer_id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Bienvenido de vuelta! Continuemos donde lo dejamos.',
                'existing_user' => true,
                'data' => [
                    'lead_id' => $existingLead['id'],
                    'email' => $existingLead['email'],
                    'referral_code' => $existingLead['referral_code'],
                    'conversation_history' => $conversationData,
                    'status' => $existingLead['status'],
                    'has_referral_code' => $hasReferralCode,
                    'referrer_id' => $existingLead['referrer_id']
                ]
            ]);
            return;
        } else {
            error_log("Creando nuevo lead");
            // Nuevo lead - obtener referrer_id si existe código
            $referrerId = null;
            if ($referralCode) {
                error_log("Buscando referrer con código: $referralCode");
                // Buscar por código de referido en userUser
                $stmt = $pdo->prepare("SELECT idUser FROM tbluser WHERE userUser = ?");
                $stmt->execute([$referralCode]);
                $referrer = $stmt->fetch();
                if ($referrer) {
                    $referrerId = $referrer['idUser'];
                    error_log("Referrer encontrado: $referrerId");
                } else {
                    error_log("Referrer no encontrado para código: $referralCode");
                }
            }
            
            // Crear nuevo lead
            error_log("Insertando nuevo lead en BD");
            
            // Verificar que el session_id no exista
            $stmt = $pdo->prepare("SELECT id FROM chat_leads WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            $existingSession = $stmt->fetch();
            
            if ($existingSession) {
                // Generar nuevo session_id único
                $sessionId = 'session_' . time() . '_' . uniqid() . '_' . mt_rand(1000, 9999);
                error_log("Session_id duplicado, generando nuevo: $sessionId");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO chat_leads (email, session_id, referral_code, referrer_id, status) 
                VALUES (?, ?, ?, ?, 'active')
            ");
            
            $stmt->execute([$email, $sessionId, $referralCode, $referrerId]);
            error_log("Lead creado correctamente con session_id: $sessionId");
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'conversation_history' => []
                ]
            ]);
        }
        
    } catch (Exception $e) {
        error_log("=== ERROR DETALLADO GUARDANDO LEAD ===");
        error_log("Error: " . $e->getMessage());
        error_log("File: " . $e->getFile());
        error_log("Line: " . $e->getLine());
        error_log("Trace: " . $e->getTraceAsString());
        error_log("Email: $email");
        error_log("SessionId: $sessionId");
        error_log("ReferralCode: $referralCode");
        throw new Exception('Error guardando información: ' . $e->getMessage());
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
    if ($result && !empty($result['conversation_data'])) {
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
 * Guardar mensaje en historial automáticamente
 */
function saveMessageToHistory($sessionId, $sender, $message) {
    global $pdo;
    
    try {
        // Obtener conversación actual
        $stmt = $pdo->prepare("SELECT conversation_data FROM chat_leads WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $lead = $stmt->fetch();
        
        if ($lead) {
            $conversationData = [];
            if (!empty($lead['conversation_data'])) {
                $conversationData = json_decode($lead['conversation_data'], true) ?? [];
            }
            
            // Agregar nuevo mensaje
            $conversationData[] = [
                'sender' => $sender,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Actualizar en base de datos
            $stmt = $pdo->prepare("UPDATE chat_leads SET conversation_data = ? WHERE session_id = ?");
            $stmt->execute([json_encode($conversationData), $sessionId]);
            
            error_log("Mensaje guardado en historial: $sender - " . substr($message, 0, 50));
        }
        
    } catch (Exception $e) {
        error_log("Error guardando mensaje en historial: " . $e->getMessage());
    }
}

/**
 * Obtener respuesta FAQ automatizada
 */
function handleFAQResponse($input) {
    $message = strtolower(trim($input['message'] ?? ''));
    
    if (empty($message)) {
        echo json_encode([
            'success' => true,
            'data' => [
                'response' => '¿En qué puedo ayudarte? Puedo responder sobre Mizton, precios, funcionamiento o seguridad.',
                'requires_human' => false,
                'powered_by' => 'faq'
            ]
        ]);
        return;
    }
    
    // FAQ básicas de Mizton - Más conversacionales y con más variaciones
    $faqs = [
        // Saludos
        'hola' => '¡Hola! 👋 Bienvenido a Mizton. Soy tu asistente virtual y estoy aquí para ayudarte con cualquier pregunta sobre nuestra plataforma.',
        'buenos dias' => '¡Buenos días! 🌅 ¡Qué gusto tenerte aquí! Soy el asistente de Mizton y estoy listo para resolver todas tus dudas.',
        'buenas tardes' => '¡Buenas tardes! ☀️ Perfecto momento para conocer sobre Mizton. ¿En qué puedo ayudarte hoy?',
        'buenas noches' => '¡Buenas noches! 🌙 Aunque sea tarde, estoy aquí 24/7 para ayudarte con Mizton. ¿Qué te gustaría saber?',
        
        // Qué es Mizton
        'que es mizton' => 'Mizton es una plataforma innovadora que ofrece membresías garantizadas con recuperación del 100% más ganancias adicionales. ¡Es como tener lo mejor de ambos mundos! 🚀',

        // Funcionamiento
        'como funciona' => 'Nuestro sistema funciona así: 1) Te registras, 2) Adquieres un paquete de participación (Membresía), 3) Accedes a los dividendos globales de Mizton, 4) Al final del período si decides no continuar, recuperas el 100% de tu inversión inicial + el incentivo de al menos un 15%. ¡Es así de simple! 🎯',
        
        // Membresías y tokens
        'membresia' => 'Las membresías son tu entrada a los dividendos globales de Mizton. Cada membresía incluye tokens corporativos que generan ganancias. ¿Qué te interesa saber?',
        
        // Ganancias
        'cuanto puedo ganar' => 'Las ganancias varían según la cantidad de Tokens que poseas. Recuerda que hablamos de ganancias globales, más bonos adicionales por referidos. ¿Te interesa conocer los detalles específicos? 💰',
        
        // Seguridad
        'es seguro' => 'Absolutamente. Mizton garantiza la recuperación del 100% de tu compra inicial más un incentivo mínimo del 15%. Además, contamos con un sistema de respaldo sólido y transparente. Tu seguridad financiera es nuestra prioridad. 🛡️',
        
        // Registro y inicio
        'como empezar' => 'Para empezar es muy fácil: 1) Regístrate en nuestra plataforma, 2) Obtén tu primera membresía, 3) ¡Comienza a generar ganancias! ¿Te ayudo con el proceso de registro? 🚀',
        
        // Precios
        'precio' => 'Desde un paquete de $50 USD ya estás participando de los dividendos globales de Mizton. ¿Te guío para obtener tu registro y poder adquirir tu primera membresía? 💵'
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
    
    // Fallback solo si no hay respuesta FAQ (la IA manejará el resto)
    if (!$response) {
        // Si no hay respuesta FAQ, devolver null para que la IA tome el control
        echo json_encode([
            'success' => false,
            'data' => [
                'response' => null,
                'requires_ai' => true,
                'message' => 'No FAQ match - escalate to AI'
            ]
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'response' => $response,
            'requires_human' => $requiresHuman,
            'powered_by' => 'faq'
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
    
    error_log("=== ESCALAMIENTO INICIADO ===");
    error_log("Input: " . json_encode($input));
    
    $sessionId = trim($input['session_id'] ?? '');
    $email = trim($input['email'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    
    if (empty($sessionId)) {
        error_log("ERROR: session_id vacío");
        throw new Exception('session_id es requerido');
    }
    
    error_log("Session ID: " . $sessionId);
    
    try {
        // Obtener información del lead
        error_log("Buscando lead con session_id: " . $sessionId);
        $stmt = $pdo->prepare("SELECT * FROM chat_leads WHERE session_id = ?");
        $stmt->execute([$sessionId]);
        $lead = $stmt->fetch();
        
        if (!$lead) {
            error_log("ERROR: Lead no encontrado para session_id: " . $sessionId);
            throw new Exception('Sesión no encontrada');
        }
        
        error_log("Lead encontrado: " . json_encode($lead));
        
        // Obtener información del referidor si existe
        $referrerInfo = null;
        if ($lead['referrer_id']) {
            error_log("Buscando referrer_id: " . $lead['referrer_id']);
            $stmt = $pdo->prepare("
                SELECT nameUser, emailUser, celularUser, countryUser, waUser, landing_preference 
                FROM tbluser 
                WHERE idUser = ?
            ");
            $stmt->execute([$lead['referrer_id']]);
            $referrerInfo = $stmt->fetch();
            error_log("Referrer info: " . json_encode($referrerInfo));
        } else {
            error_log("No hay referrer_id");
        }
        
        // Determinar método de contacto
        error_log("Determinando método de contacto...");
        $contactMethod = determineHumanContactMethod($referrerInfo);
        error_log("Método de contacto: " . json_encode($contactMethod));
        
        // Actualizar estado del lead (con manejo de errores)
        try {
            $stmt = $pdo->prepare("UPDATE chat_leads SET status = 'escalated_to_human', updated_at = NOW() WHERE session_id = ?");
            $stmt->execute([$sessionId]);
        } catch (Exception $updateError) {
            error_log("Error updating lead status: " . $updateError->getMessage());
            // Continuar aunque falle la actualización
        }
        
        // Registrar escalamiento (opcional)
        try {
            logEscalation($sessionId, $lead['email'], $contactMethod);
        } catch (Exception $logError) {
            error_log("Error logging escalation: " . $logError->getMessage());
            // Continuar aunque falle el logging
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'escalated' => true,
                'contact_method' => $contactMethod['type'],
                'contact_info' => $contactMethod['info'],
                'message' => $contactMethod['message'],
                'referrer_name' => $contactMethod['referrer_name'] ?? null
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
    if ($referrerInfo && $referrerInfo['landing_preference'] == 1 && $referrerInfo['waUser'] == 1 && $referrerInfo['celularUser']) {
{{ ... }}
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

/**
 * Crear tabla chat_leads si no existe
 */
function createChatLeadsTable() {
    global $pdo;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS chat_leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            referral_code VARCHAR(50) NULL,
            referrer_id INT NULL,
            session_id VARCHAR(100) NULL UNIQUE,
            conversation_data LONGTEXT NULL,
            status ENUM('active', 'email_captured', 'converted', 'abandoned', 'escalated_to_human') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_referral_code (referral_code),
            INDEX idx_referrer_id (referrer_id),
            INDEX idx_session_id (session_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        error_log("Tabla chat_leads verificada/creada correctamente");
        
    } catch (Exception $e) {
        error_log("Error creando tabla chat_leads: " . $e->getMessage());
    }
}

/**
 * Verificar código de referido
 */
function handleVerifyReferralCode($input) {
    global $pdo;
    
    $referralCode = trim($input['referral_code'] ?? '');
    
    if (empty($referralCode)) {
        throw new Exception('referral_code es requerido');
    }
    
    try {
        // Buscar el código en tbluser
        $stmt = $pdo->prepare("SELECT idUser, nameUser FROM tbluser WHERE userUser = ?");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch();
        
        if ($referrer) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'referrer_id' => $referrer['idUser'],
                    'referrer_name' => $referrer['nameUser']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Código de referido no encontrado',
                'data' => null
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error verificando código de referido: " . $e->getMessage());
        throw new Exception('Error verificando código de referido');
    }
}

/**
 * Actualizar código de referido en lead existente
 */
function handleUpdateReferralCode($input) {
    global $pdo;
    
    $sessionId = trim($input['session_id'] ?? '');
    $referralCode = trim($input['referral_code'] ?? '');
    
    if (empty($sessionId) || empty($referralCode)) {
        throw new Exception('session_id y referral_code son requeridos');
    }
    
    try {
        // Obtener referrer_id
        $stmt = $pdo->prepare("SELECT idUser FROM tbluser WHERE userUser = ?");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch();
        
        if (!$referrer) {
            throw new Exception('Código de referido no válido');
        }
        
        // Actualizar el lead
        $stmt = $pdo->prepare("
            UPDATE chat_leads 
            SET referral_code = ?, referrer_id = ?, updated_at = NOW() 
            WHERE session_id = ?
        ");
        $stmt->execute([$referralCode, $referrer['idUser'], $sessionId]);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'referral_code' => $referralCode,
                'referrer_id' => $referrer['idUser']
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error actualizando código de referido: " . $e->getMessage());
        throw new Exception('Error actualizando código de referido');
    }
}

/**
 * Debug de configuración de IA
 */
function handleDebugAIConfig() {
    $config = AIConfig::getOpenAIConfig();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'ai_enabled' => $_ENV['AI_ENABLED'] ?? 'false',
            'openai_key_exists' => !empty($config['api_key']),
            'openai_key_length' => strlen($config['api_key'] ?? ''),
            'ai_model' => $config['model'],
            'ai_max_tokens' => $config['max_tokens'],
            'ai_temperature' => $config['temperature'],
            'env_file_exists' => file_exists(__DIR__ . '/../.env'),
            'config_loaded' => !empty($_ENV)
        ]
    ]);
}

/**
 * Generar embeddings para la base de conocimiento
 */
function handleGenerateEmbeddings() {
    try {
        require_once __DIR__ . '/embeddings-handler.php';
        
        $embeddings = new EmbeddingsHandler();
        $count = $embeddings->createKnowledgeEmbeddings();
        
        echo json_encode([
            'success' => true,
            'message' => "Embeddings generados exitosamente",
            'data' => [
                'vectors_created' => $count,
                'status' => 'completed'
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error generating embeddings: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Error al generar embeddings: ' . $e->getMessage(),
            'data' => null
        ]);
    }
}
