<?php
/**
 * Configuración de IA para Chat de Mizton
 * Centraliza toda la configuración relacionada con IA
 */

class AIConfig {
    
    /**
     * Verificar si la IA está habilitada
     */
    public static function isEnabled() {
        return ($_ENV['AI_ENABLED'] ?? 'false') === 'true';
    }
    
    /**
     * Obtener configuración de OpenAI
     */
    public static function getOpenAIConfig() {
        return [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
            'model' => $_ENV['AI_MODEL'] ?? 'gpt-3.5-turbo',
            'max_tokens' => intval($_ENV['AI_MAX_TOKENS'] ?? 300),
            'temperature' => floatval($_ENV['AI_TEMPERATURE'] ?? 0.7)
        ];
    }
    
    /**
     * Obtener prompt del sistema personalizado para Mizton
     */
    public static function getSystemPrompt() {
        return "Eres el asistente virtual oficial de Mizton, una plataforma innovadora de membresías corporativas.

PERSONALIDAD:
- Amigable, profesional y entusiasta
- Experto en la plataforma Mizton
- Orientado a ayudar y resolver dudas
- Usa emojis ocasionalmente para ser más cercano

INSTRUCCIONES:
1. Responde SOLO sobre temas relacionados con Mizton
2. Si no sabes algo específico, ofrece conectar con un asesor humano
3. Mantén respuestas concisas pero informativas (máximo 2-3 párrafos)
4. Enfócate en los beneficios y la seguridad de Mizton
5. Siempre menciona la garantía del 100% + 15% mínimo
6. Si preguntan por precios, menciona que desde $50 USD ya participan

ESCALAMIENTO A HUMANO:
Si el usuario solicita hablar con un asesor, contacto humano, o necesita ayuda personalizada, responde EXACTAMENTE:
'ESCALATE_TO_HUMAN: [razón del escalamiento]'

Ejemplos de cuándo escalar:
- 'quiero hablar con alguien'
- 'necesito un asesor'
- 'contactar con humano'
- 'hablar con una persona'
- 'información más detallada'
- 'asesoramiento personalizado'

TEMAS PROHIBIDOS:
- No des consejos financieros específicos
- No compares con otras plataformas de inversión
- No prometas ganancias específicas más allá de lo establecido
- No discutas temas no relacionados con Mizton

Si el usuario pregunta algo fuera de tu conocimiento sobre Mizton, responde:
'ESCALATE_TO_HUMAN: Información específica requerida'";
    }
    
    /**
     * Obtener configuración de fallback
     */
    public static function getFallbackConfig() {
        return [
            'use_faq' => true,
            'escalate_after_attempts' => 3,
            'escalation_message' => 'Parece que necesitas información más específica. ¿Te gustaría hablar con uno de nuestros asesores especializados? Ellos podrán resolver todas tus dudas personalizadas sobre Mizton.'
        ];
    }
    
    /**
     * Obtener métricas de uso de IA
     */
    public static function logAIUsage($sessionId, $message, $response, $model = 'faq') {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ai_usage_logs (session_id, user_message, ai_response, model_used, created_at) 
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                ai_response = VALUES(ai_response), 
                model_used = VALUES(model_used),
                updated_at = NOW()
            ");
            
            $stmt->execute([$sessionId, $message, $response, $model]);
        } catch (Exception $e) {
            error_log("Error logging AI usage: " . $e->getMessage());
        }
    }
}

/**
 * Crear tabla de logs de IA si no existe
 */
function createAILogsTable() {
    global $pdo;
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_usage_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(100) NOT NULL,
                user_message TEXT NOT NULL,
                ai_response TEXT NOT NULL,
                model_used VARCHAR(50) DEFAULT 'faq',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_model (model_used),
                INDEX idx_created (created_at)
            )
        ");
    } catch (Exception $e) {
        error_log("Error creating AI logs table: " . $e->getMessage());
    }
}

/**
 * Crear tabla de escalamientos si no existe
 */
function createEscalationLogsTable() {
    global $pdo;
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS escalation_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                contact_method VARCHAR(50) NOT NULL,
                contact_info JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session (session_id),
                INDEX idx_email (email),
                INDEX idx_method (contact_method),
                INDEX idx_created (created_at)
            )
        ");
    } catch (Exception $e) {
        error_log("Error creating escalation logs table: " . $e->getMessage());
    }
}
?>
