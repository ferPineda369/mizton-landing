<?php
/**
 * Handler de IA para Chat Inteligente - FASE 2
 * Integración con OpenAI GPT para respuestas contextuales
 */

require_once '../database.php';
require_once '../config/ai-config.php';

class MiztonAIHandler {
    private $config;
    private $base_knowledge;
    
    public function __construct() {
        $this->config = AIConfig::getOpenAIConfig();
        $this->loadKnowledgeBase();
        
        // Crear tabla de logs si no existe
        createAILogsTable();
    }
    
    private function loadKnowledgeBase() {
        // Cargar base de conocimiento desde archivo
        $this->base_knowledge = AIConfig::getKnowledgeBase();
        error_log("AI: Knowledge base loaded from file, length: " . strlen($this->base_knowledge));
    }
    
    /**
     * Obtener conocimiento relevante basado en la consulta del usuario
     */
    private function getRelevantKnowledge($message) {
        // TEMPORAL: Usar palabras clave directamente para probar nueva información
        // TODO: Reactivar embeddings cuando estén completamente actualizados
        return $this->getKeywordBasedKnowledge($message);
        
        // Intentar usar embeddings si están disponibles
        /*if (class_exists('EmbeddingsHandler')) {
            try {
                $embeddings = new EmbeddingsHandler();
                return $embeddings->findRelevantKnowledge($message, 6000);
            } catch (Exception $e) {
                error_log("AI: Embeddings failed, using keyword matching: " . $e->getMessage());
            }
        }
        
        // Fallback: búsqueda por palabras clave
        return $this->getKeywordBasedKnowledge($message);*/
    }
    
    /**
     * Búsqueda de conocimiento basada en palabras clave
     */
    private function getKeywordBasedKnowledge($message) {
        $fullKnowledge = AIConfig::getKnowledgeBase(50000); // Sin límite para buscar
        $message = strtolower($message);
        
        // Palabras clave y sus secciones relacionadas - Actualizado 2025
        $keywordMap = [
            // CONCEPTOS BÁSICOS
            'que es mizton' => ['EXPLICACION SIMPLE', 'CONCEPTO GENERAL'],
            'como gano' => ['EXPLICACION SIMPLE'],
            'como se sustenta' => ['EXPLICACION SIMPLE'],
            'precio' => ['PRECIOS Y ACCESIBILIDAD', 'MODELO DE NEGOCIO DETALLADO'],
            'costo' => ['PRECIOS Y ACCESIBILIDAD', 'MODELO DE NEGOCIO DETALLADO'],
            'paquete' => ['MODELO DE NEGOCIO DETALLADO', 'QUÉ RECIBES CON LA MEMBRESÍA'],
            'token' => ['MECÁNICA DEL TOKEN CORPORATIVO', 'QUÉ RECIBES CON LA MEMBRESÍA'],
            'ganancia' => ['GARANTÍAS Y SEGURIDAD', 'EXPLICACION SIMPLE'],
            'ganancia' => ['MODELO DE NEGOCIO DETALLADO', 'QUÉ RECIBES CON LA MEMBRESÍA'],
            'funciona' => ['CÓMO FUNCIONA', 'EXPLICACION SIMPLE'],
            'membresía' => ['CONCEPTO GENERAL', 'QUÉ RECIBES CON LA MEMBRESÍA'],
            
            // SISTEMA DE REFERIDOS
            'referido' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            'patrocinio' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            'bono patrocinio' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            'red' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            'directo' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            'segundo nivel' => ['SISTEMA DE REFERIDOS Y BONIFICACIONES'],
            
            // TOKENIZACIÓN Y RWA
            'tokenización' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'tokenizar' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'rwa' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'real world assets' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'activos reales' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'blockchain' => ['CONCEPTOS DE TOKENIZACIÓN', 'EDUCACIÓN Y ACOMPAÑAMIENTO'],
            'blackrock' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'larry fink' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'citi' => ['CONCEPTOS DE TOKENIZACIÓN'],
            
            // PROYECCIONES Y MERCADO
            '2030' => ['CONCEPTOS DE TOKENIZACIÓN'],
            '16 billones' => ['CONCEPTOS DE TOKENIZACIÓN'],
            '24 billones' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'proyeccion' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'proyecciones' => ['CONCEPTOS DE TOKENIZACIÓN'],
            'mercado' => ['CONCEPTOS DE TOKENIZACIÓN'],
            
            // EDUCACIÓN Y SOPORTE
            'educacion' => ['EDUCACIÓN Y ACOMPAÑAMIENTO'],
            'capacitacion' => ['EDUCACIÓN Y ACOMPAÑAMIENTO'],
            'wallet' => ['EDUCACIÓN Y ACOMPAÑAMIENTO', 'RIESGOS Y CONSIDERACIONES'],
            'contacto' => ['CONTACTO Y SOPORTE'],
            'whatsapp' => ['CONTACTO Y SOPORTE'],
            'zoom' => ['PRESENTACIONES'],
            'presentacion' => ['PRESENTACIONES'],
            'presentaciones' => ['PRESENTACIONES'],
            'lunes a viernes' => ['PRESENTACIONES'],
            '7:00 pm' => ['PRESENTACIONES'],
            
            // SEGURIDAD Y GARANTÍAS
            'garantía' => ['GARANTÍAS Y SEGURIDAD'],
            'seguridad' => ['GARANTÍAS Y SEGURIDAD', 'PREGUNTAS FRECUENTES ADICIONALES'],
            'recuperar' => ['GARANTÍAS Y SEGURIDAD', 'PREGUNTAS FRECUENTES ADICIONALES'],
            'riesgo' => ['RIESGOS Y CONSIDERACIONES'],
            'salir del sistema' => ['PREGUNTAS FRECUENTES ADICIONALES'],
            
            // MODELO DE NEGOCIO
            'dca' => ['MODELO DE NEGOCIO DETALLADO'],
            'dollar cost' => ['MODELO DE NEGOCIO DETALLADO'],
            'equilibrio' => ['MODELO DE NEGOCIO DETALLADO'],
            'paquetes equilibrio' => ['MODELO DE NEGOCIO DETALLADO'],
            'holding' => ['MECÁNICA DEL TOKEN CORPORATIVO'],
            'holdear' => ['MECÁNICA DEL TOKEN CORPORATIVO'],
            'vender' => ['MECÁNICA DEL TOKEN CORPORATIVO'],
            'capitalizacion' => ['MECÁNICA DEL TOKEN CORPORATIVO'],
            'union fuerzas' => ['MECÁNICA DEL TOKEN CORPORATIVO'],
            
            // PREGUNTAS FRECUENTES
            'criptomoneda' => ['PREGUNTAS FRECUENTES ADICIONALES', 'CONCEPTO GENERAL'],
            'conocimientos tecnicos' => ['PREGUNTAS FRECUENTES ADICIONALES'],
            'necesito conocimientos' => ['PREGUNTAS FRECUENTES ADICIONALES'],
            
            // REGISTRO Y PROCESO
            'registro' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)', 'PROCESO DE REGISTRO'],
            'como me registro' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)', 'PROCESO DE REGISTRO'],
            'registrarse' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'unirse' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'unete ahora' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'quiero unirme' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'codigo invitacion' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'codigo de invitacion' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'invitacion' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'correo electronico' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'email' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'video tutoriales' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'tutoriales' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'pertenecer comunidad' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'comunidad' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)', 'CONCEPTO GENERAL'],
            'como pertenecer' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'pasos registro' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            'proceso registro' => ['PROCESO PARA REGISTRARSE (Pertecener a la comunidad)'],
            
            // CAMBIO FINANCIERO GLOBAL
            '2025' => ['EL CAMBIO FINANCIERO GLOBAL'],
            'cambio financiero' => ['EL CAMBIO FINANCIERO GLOBAL'],
            'banca tradicional' => ['EL CAMBIO FINANCIERO GLOBAL'],
            'bancos centrales' => ['EL CAMBIO FINANCIERO GLOBAL'],
            'dinero efectivo' => ['EL CAMBIO FINANCIERO GLOBAL'],
            'gobierno' => ['EL CAMBIO FINANCIERO GLOBAL']
        ];
        
        // Encontrar secciones relevantes
        $relevantSections = [];
        error_log("AI DEBUG: Searching for keywords in message: " . $message);
        foreach ($keywordMap as $keyword => $sections) {
            if (strpos($message, $keyword) !== false) {
                error_log("AI DEBUG: Found keyword '$keyword', adding sections: " . implode(', ', $sections));
                $relevantSections = array_merge($relevantSections, $sections);
            }
        }
        error_log("AI DEBUG: Total relevant sections found: " . implode(', ', array_unique($relevantSections)));
        
        // Si no hay palabras clave específicas, usar secciones básicas
        if (empty($relevantSections)) {
            $relevantSections = ['CONCEPTO GENERAL', 'CÓMO FUNCIONA', 'PRECIOS Y ACCESIBILIDAD'];
        }
        
        // Extraer secciones relevantes
        $selectedContent = [];
        foreach (array_unique($relevantSections) as $section) {
            $pattern = '/## ' . preg_quote($section, '/') . '\s*(.*?)(?=## |\z)/s';
            if (preg_match($pattern, $fullKnowledge, $matches)) {
                $selectedContent[] = "## " . $section . "\n" . trim($matches[1]);
            }
        }
        
        $result = implode("\n\n", $selectedContent);
        error_log("AI: Selected " . count($selectedContent) . " sections for query");
        
        return $result ?: AIConfig::getKnowledgeBase(12000);
    }
    
    public function getAIResponse($message, $conversationHistory = [], $sessionId = '') {
        if (empty($this->config['api_key'])) {
            error_log("AI: API key is empty");
            $fallbackResponse = $this->getFallbackResponse($message);
            AIConfig::logAIUsage($sessionId, $message, $fallbackResponse, 'faq_fallback');
            return $fallbackResponse;
        }
        
        // Verificar límites de costo antes de hacer la consulta
        require_once __DIR__ . '/cost-monitor.php';
        $estimatedTokens = 6000; // Estimación conservadora
        
        if (!checkAICostLimits($estimatedTokens)) {
            error_log("AI: Daily cost limit reached, using fallback");
            $fallbackResponse = $this->getFallbackResponse($message);
            AIConfig::logAIUsage($sessionId, $message, $fallbackResponse, 'cost_limit_fallback');
            return $fallbackResponse;
        }
        
        error_log("AI: Starting OpenAI call for message: " . substr($message, 0, 50) . "...");
        
        $context = $this->buildContext($message, $conversationHistory);
        
        // Obtener conocimiento relevante específico para esta consulta
        $relevantKnowledge = $this->getRelevantKnowledge($message);
        
        $systemContent = AIConfig::getSystemPrompt() . "\n\nINFORMACIÓN RELEVANTE:\n{$relevantKnowledge}";
        
        $payload = [
            'model' => $this->config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemContent
                ],
                [
                    'role' => 'user', 
                    'content' => $context
                ]
            ],
            'max_tokens' => $this->config['max_tokens'],
            'temperature' => $this->config['temperature']
        ];
        
        error_log("AI: Payload prepared, calling OpenAI...");
        
        $response = $this->callOpenAI($payload);
        
        if ($response) {
            error_log("AI: OpenAI response received successfully");
            AIConfig::logAIUsage($sessionId, $message, $response, $this->config['model']);
            
            // Detectar si la IA solicita escalamiento
            if (strpos($response, 'ESCALATE_TO_HUMAN:') === 0) {
                error_log("AI: Escalamiento detectado, generando WhatsApp link");
                return $this->generateWhatsAppEscalation($sessionId, $response);
            }
            
            return $response;
        } else {
            error_log("AI: OpenAI call failed, using fallback");
            $fallbackResponse = $this->getFallbackResponse($message);
            AIConfig::logAIUsage($sessionId, $message, $fallbackResponse, 'faq_error_fallback');
            return $fallbackResponse;
        }
    }
    
    private function buildContext($message, $history) {
        $context = "Mensaje actual: {$message}\n\n";
        
        if (!empty($history)) {
            $context .= "Historial de conversación:\n";
            foreach (array_slice($history, -3) as $msg) { // Últimos 3 mensajes
                $context .= "{$msg['sender']}: {$msg['message']}\n";
            }
        }
        
        return $context;
    }
    
    private function callOpenAI($payload) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['api_key']
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        error_log("AI: Making cURL request to OpenAI...");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("OpenAI cURL Error: " . $curlError);
            return null;
        }
        
        error_log("AI: OpenAI HTTP Response Code: " . $httpCode);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                error_log("AI: Successfully parsed OpenAI response");
                return $data['choices'][0]['message']['content'];
            } else {
                error_log("AI: Invalid OpenAI response structure: " . json_encode($data));
                return null;
            }
        }
        
        error_log("OpenAI API Error: HTTP {$httpCode} - " . substr($response, 0, 500));
        return null;
    }
    
    private function getFallbackResponse($message) {
        // FAQ básicas como fallback (sincronizadas con chat-handler.php)
        $message = strtolower($message);
        
        $faqs = [
            'hola' => '¡Hola! 👋 Bienvenido a Mizton. Soy tu asistente virtual y estoy aquí para ayudarte con cualquier pregunta sobre nuestra plataforma.',
            
            'que es mizton' => 'Mizton es una plataforma innovadora que ofrece membresías garantizadas con recuperación del 100% más ganancias adicionales.',
            
            'como funciona' => 'Nuestro sistema funciona así: 1) Te registras, 2) Adquieres un paquete de participación (Membresía) desde $20 USD, 3) Accedes a las ganancias globales de Mizton, 4) Al final del período si decides no continuar, recuperas el 100% de tu inversión inicial. ¡Es así de simple!',
            
            'cuanto puedo ganar' => 'Las ganancias varían según la cantidad de Tokens que poseas. Recuerda que hablamos de ganancias globales, más bonos adicionales. ¿Te interesa conocer los detalles específicos?',
            
            'es seguro' => 'Absolutamente. Mizton garantiza la recuperación del 100% de tu inversión inicial. Además, contamos con un sistema de respaldo sólido y transparente. Tu seguridad financiera es nuestra prioridad.',
            
            'como empezar' => 'Para empezar es muy fácil: 1) Regístrate en nuestra plataforma, 2) Obtén tu primera membresía, 3) ¡Comienza a generar ganancias!. ¿Te ayudo con el registro?',
            
            'precio' => 'Desde un paquete de $20 USD ya estás participando de las ganancias globales de Mizton. ¿Te gustaría conocer más detalles?',
            
            'hablar con humano' => 'Por supuesto! Te voy a conectar con uno de nuestros asesores especializados. Por favor espera un momento mientras te redirijo...',
            'asesor humano' => 'Perfecto! Te conectaré con un asesor humano especializado. Un momento por favor...'
        ];
        
        foreach ($faqs as $keyword => $answer) {
            if (strpos($message, $keyword) !== false) {
                return $answer;
            }
        }
        
        return 'Entiendo tu pregunta. Para darte la mejor respuesta, ¿te gustaría que te conecte con uno de nuestros asesores especializados?';
    }
    
    private function generateWhatsAppEscalation($sessionId, $aiResponse) {
        global $pdo;
        
        try {
            // Obtener información del lead y su referrer
            $stmt = $pdo->prepare("
                SELECT cl.*, u.celularUser, u.nameUser, u.landing_preference 
                FROM chat_leads cl 
                LEFT JOIN tbluser u ON cl.referrer_id = u.idUser 
                WHERE cl.session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $leadData = $stmt->fetch();
            
            // Verificar si el referrer existe y tiene configurada atención personal
            if ($leadData && !empty($leadData['celularUser']) && $leadData['landing_preference'] == 1) {
                // Referrer tiene atención personal activada - usar su WhatsApp
                $whatsappNumber = $leadData['celularUser'];
                $referrerName = $leadData['nameUser'] ?? 'tu patrocinador';
                $message = "¡Perfecto! Te voy a conectar con {$referrerName}, quien te invitó a Mizton. Él tiene configurada la atención personalizada y podrá brindarte asesoramiento directo para resolver todas tus dudas específicas.";
                error_log("AI: Using referrer WhatsApp (personal attention enabled): {$whatsappNumber}");
            } else {
                // Sin referrer O referrer sin atención personal - usar WhatsApp oficial
                $whatsappNumber = $_ENV['DEFAULT_WHATSAPP'] ?? '5212215695942';
                
                if ($leadData && !empty($leadData['celularUser']) && $leadData['landing_preference'] != 1) {
                    $message = "Te voy a conectar con nuestro equipo oficial de asesores de Mizton. Tu patrocinador ha configurado que las consultas se dirijan a nuestro equipo especializado para brindarte la mejor atención.";
                    error_log("AI: Referrer exists but personal attention disabled, using official WhatsApp");
                } else {
                    $message = "¡Perfecto! Te voy a conectar con uno de nuestros asesores especializados de Mizton. Ellos podrán brindarte asesoramiento personalizado y resolver todas tus dudas específicas.";
                    error_log("AI: No referrer found, using official WhatsApp");
                }
            }
            
            // Generar mensaje para WhatsApp
            $whatsappMessage = urlencode("Hola! Vengo del chat de Mizton y me gustaría recibir más información sobre las membresías corporativas. ¡Gracias!");
            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
            
            $finalMessage = $message . "\n\n👤 [Contactar Asesor]({$whatsappLink})\n\n¡Gracias por tu interés en Mizton! 🚀";
            
            error_log("AI: WhatsApp escalation generated for number: {$whatsappNumber}");
            return $finalMessage;
            
        } catch (Exception $e) {
            error_log("AI: Error generating WhatsApp escalation: " . $e->getMessage());
            
            // Fallback a WhatsApp oficial
            $defaultWhatsApp = $_ENV['DEFAULT_WHATSAPP'] ?? '5212215695942';
            $whatsappMessage = urlencode("Hola! Vengo del chat de Mizton y me gustaría recibir más información. ¡Gracias!");
            $whatsappLink = "https://wa.me/{$defaultWhatsApp}?text={$whatsappMessage}";
            
            return "Te conectaré con nuestro equipo de asesores especializados:\n\n👤 [Contactar Asesor Mizton]({$whatsappLink})\n\n¡Gracias por tu interés en Mizton! 🚀";
        }
    }
}

// Función para usar en chat-handler.php
function getAIResponse($message, $sessionId) {
    global $pdo;
    
    // Obtener historial de conversación
    $stmt = $pdo->prepare("SELECT conversation_data FROM chat_leads WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $result = $stmt->fetch();
    
    $history = [];
    if ($result && $result['conversation_data']) {
        $history = json_decode($result['conversation_data'], true) ?? [];
    }
    
    $aiHandler = new MiztonAIHandler();
    return $aiHandler->getAIResponse($message, $history, $sessionId);
}
?>
