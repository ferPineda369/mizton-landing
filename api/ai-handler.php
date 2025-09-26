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
        $this->base_knowledge = "
        INFORMACIÓN ACTUALIZADA SOBRE MIZTON:
        
        CONCEPTO GENERAL:
        Mizton es una plataforma innovadora que ofrece membresías garantizadas con recuperación del 100% más ganancias adicionales.
        
        CÓMO FUNCIONA:
        1. Te registras en la plataforma
        2. Adquieres un paquete de participación (Membresía)
        3. Accedes a los dividendos globales de Mizton
        4. Al final del período si decides no continuar, recuperas el 100% de tu compra inicial + el incentivo de al menos un 15%
        
        QUÉ RECIBES CON LA MEMBRESÍA:
        - Paquete de Tokens Corporativos
        - Acceso a dividendos globales de Mizton
        - Ganancias según la cantidad de Tokens que poseas
        - Bonos adicionales por referidos
        
        PRECIOS Y ACCESIBILIDAD:
        - Desde $50 USD ya estás participando de los dividendos globales
        - Puedes adquirir más paquetes para obtener más ganancias
        - Sistema escalable según tu capacidad de compra
        
        GARANTÍAS Y SEGURIDAD:
        - 100% de recuperación de compra inicial
        - Incentivo mínimo garantizado del 15%
        - Sistema de respaldo sólido y transparente
        - Seguridad financiera como prioridad
        
        PROCESO DE REGISTRO:
        1. Necesitas ser invitado por uno de nuestros Miembros
        2. Registro simple con email
        3. Acceso a panel personal
        4. Adquisición de membresía
        5. Inicio de generación de ganancias
        
        SISTEMA DE REFERIDOS:
        - Cada usuario obtiene un código único
        - Bonos por referir nuevos miembros
        - Estructura multinivel
        - Ganancias globales compartidas
        
        CONTACTO Y SOPORTE:
        - Chat en vivo en la plataforma
        - WhatsApp personalizado
        - Email de soporte
        - Asesores especializados disponibles
        ";
    }
    
    public function getAIResponse($message, $conversationHistory = [], $sessionId = '') {
        if (empty($this->config['api_key'])) {
            error_log("AI: API key is empty");
            $fallbackResponse = $this->getFallbackResponse($message);
            AIConfig::logAIUsage($sessionId, $message, $fallbackResponse, 'faq_fallback');
            return $fallbackResponse;
        }
        
        error_log("AI: Starting OpenAI call for message: " . substr($message, 0, 50) . "...");
        
        $context = $this->buildContext($message, $conversationHistory);
        
        $payload = [
            'model' => $this->config['model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => AIConfig::getSystemPrompt() . "\n\nINFORMACIÓN BASE:\n{$this->base_knowledge}"
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
