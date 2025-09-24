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
            $fallbackResponse = $this->getFallbackResponse($message);
            AIConfig::logAIUsage($sessionId, $message, $fallbackResponse, 'faq_fallback');
            return $fallbackResponse;
        }
        
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
        
        $response = $this->callOpenAI($payload);
        
        if ($response) {
            AIConfig::logAIUsage($sessionId, $message, $response, $this->config['model']);
            return $response;
        } else {
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
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['choices'][0]['message']['content'] ?? null;
        }
        
        error_log("OpenAI API Error: HTTP {$httpCode} - {$response}");
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
