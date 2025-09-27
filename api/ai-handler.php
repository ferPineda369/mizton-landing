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
        // Intentar usar embeddings si están disponibles
        if (class_exists('EmbeddingsHandler')) {
            try {
                $embeddings = new EmbeddingsHandler();
                return $embeddings->findRelevantKnowledge($message, 6000);
            } catch (Exception $e) {
                error_log("AI: Embeddings failed, using keyword matching: " . $e->getMessage());
            }
        }
        
        // Fallback: búsqueda por palabras clave
        return $this->getKeywordBasedKnowledge($message);
    }
    
    /**
     * Búsqueda de conocimiento basada en palabras clave
     */
    private function getKeywordBasedKnowledge($message) {
        $fullKnowledge = AIConfig::getKnowledgeBase(50000); // Sin límite para buscar
        $message = strtolower($message);
        
        // Palabras clave y sus secciones relacionadas
        $keywordMap = [
            'precio' => ['PRECIOS Y ACCESIBILIDAD', 'MODELO DE NEGOCIO'],
            'costo' => ['PRECIOS Y ACCESIBILIDAD', 'MODELO DE NEGOCIO'],
            'paquete' => ['MODELO DE NEGOCIO', 'QUÉ RECIBES'],
            'token' => ['MODELO DE NEGOCIO', 'QUÉ RECIBES'],
            'ganancia' => ['GARANTÍAS Y SEGURIDAD', 'MODELO DE NEGOCIO'],
            'dividendo' => ['MODELO DE NEGOCIO', 'QUÉ RECIBES'],
            'registro' => ['PROCESO DE REGISTRO'],
            'referido' => ['SISTEMA DE REFERIDOS'],
            'contacto' => ['CONTACTO Y SOPORTE'],
            'whatsapp' => ['CONTACTO Y SOPORTE'],
            'tokenización' => ['TOKENIZACIÓN RWA'],
            'blockchain' => ['TOKENIZACIÓN RWA'],
            'blackrock' => ['TOKENIZACIÓN RWA'],
            'garantía' => ['GARANTÍAS Y SEGURIDAD'],
            'seguridad' => ['GARANTÍAS Y SEGURIDAD'],
            'recuperar' => ['GARANTÍAS Y SEGURIDAD'],
            'funciona' => ['CÓMO FUNCIONA'],
            'membresía' => ['CONCEPTO GENERAL', 'QUÉ RECIBES']
        ];
        
        // Encontrar secciones relevantes
        $relevantSections = [];
        foreach ($keywordMap as $keyword => $sections) {
            if (strpos($message, $keyword) !== false) {
                $relevantSections = array_merge($relevantSections, $sections);
            }
        }
        
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
        error_log("AI: Selected " . count($selectedContent) . " sections based on keywords");
        
        return $result ?: AIConfig::getKnowledgeBase(6000);
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
        
        $payload = [
            'model' => $this->config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => AIConfig::getSystemPrompt() . "\n\nINFORMACIÓN RELEVANTE:\n{$relevantKnowledge}"
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
            
            'como funciona' => 'Nuestro sistema funciona así: 1) Te registras, 2) Adquieres un paquete de participación (Membresía), 3) Accedes a los dividendos globales de Mizton, 4) Al final del período si decides no continuar, recuperas el 100% de tu inversión inicial + el incentivo de al menos un 15%. ¡Es así de simple!',
            
            'cuanto puedo ganar' => 'Las ganancias varían según la cantidad de Tokens que poseas. Recuerda que hablamos de ganancias globales, más bonos adicionales. ¿Te interesa conocer los detalles específicos?',
            
            'es seguro' => 'Absolutamente. Mizton garantiza la recuperación del 100% de tu inversión inicial. Además, contamos con un sistema de respaldo sólido y transparente. Tu seguridad financiera es nuestra prioridad.',
            
            'como empezar' => 'Para empezar es muy fácil: 1) Regístrate en nuestra plataforma, 2) Obtén tu primera membresía, 3) ¡Comienza a generar ganancias!. ¿Te ayudo con el registro?',
            
            'precio' => 'Desde un paquete de $50 usd ya estás participando de los dividendos globales de Mizton. ¿Te gustaría adquirir más paquetes para obtener más ganancias?',
            
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
