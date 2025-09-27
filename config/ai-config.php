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
        $promptFile = __DIR__ . '/system-prompt.txt';
        
        if (file_exists($promptFile)) {
            $prompt = file_get_contents($promptFile);
            if ($prompt !== false) {
                return trim($prompt);
            }
        }
        
        // Fallback si no se puede leer el archivo
        error_log("AI: No se pudo leer system-prompt.txt, usando fallback");
        return "Eres el asistente virtual oficial de Mizton. Responde solo sobre temas de Mizton y ofrece conectar con asesores cuando sea necesario. Si el usuario solicita contacto humano, responde: 'ESCALATE_TO_HUMAN: Solicitud de contacto'";
    }
    
    /**
     * Obtener base de conocimiento desde archivo con límite de tokens
     * CONFIGURACIÓN DE COSTOS: Ajusta maxTokens según tu presupuesto
     * 2000 tokens = ~$0.004 por consulta
     * 4000 tokens = ~$0.008 por consulta  
     * 6000 tokens = ~$0.012 por consulta
     */
    public static function getKnowledgeBase($maxTokens = 4000) {
        $knowledgeFile = __DIR__ . '/knowledge-base.md';
        
        if (file_exists($knowledgeFile)) {
            $knowledge = file_get_contents($knowledgeFile);
            if ($knowledge !== false) {
                // Truncar si excede el límite de tokens (aproximadamente 4 caracteres por token)
                $maxChars = $maxTokens * 4;
                if (strlen($knowledge) > $maxChars) {
                    // Buscar el último punto antes del límite para cortar limpiamente
                    $truncated = substr($knowledge, 0, $maxChars);
                    $lastPeriod = strrpos($truncated, '.');
                    if ($lastPeriod !== false) {
                        $knowledge = substr($truncated, 0, $lastPeriod + 1);
                    } else {
                        $knowledge = $truncated;
                    }
                    error_log("AI: Knowledge base truncated to " . strlen($knowledge) . " characters to fit token limit");
                }
                return trim($knowledge);
            }
        }
        
        // Fallback si no se puede leer el archivo
        error_log("AI: No se pudo leer knowledge-base.md, usando fallback");
        return "Mizton es una plataforma de membresías corporativas con garantía del 100% + 15% mínimo. Desde $50 USD puedes participar.";
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
