<?php
/**
 * Manejador de Contexto para Referidores
 * Genera resúmenes de conversación para enviar a referidores
 */

require_once '../database.php';

class ContextHandler {
    
    /**
     * Generar resumen de conversación para el referidor
     */
    public static function generateConversationSummary($sessionId) {
        global $pdo;
        
        try {
            // Obtener datos del lead y conversación
            $stmt = $pdo->prepare("
                SELECT cl.*, u.nameUser, u.emailUser 
                FROM chat_leads cl 
                LEFT JOIN tbluser u ON cl.referrer_id = u.idUser 
                WHERE cl.session_id = ?
            ");
            $stmt->execute([$sessionId]);
            $lead = $stmt->fetch();
            
            if (!$lead) {
                return null;
            }
            
            // Obtener conversación completa
            $conversation = json_decode($lead['conversation_data'], true) ?? [];
            
            // Generar resumen
            $summary = self::createSummary($lead, $conversation);
            
            return $summary;
            
        } catch (Exception $e) {
            error_log("Error generando resumen: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear resumen estructurado de la conversación
     */
    private static function createSummary($lead, $conversation) {
        $summary = [
            'lead_info' => [
                'email' => $lead['email'],
                'referral_code' => $lead['referral_code'],
                'status' => $lead['status'],
                'created_at' => $lead['created_at']
            ],
            'conversation_summary' => self::summarizeConversation($conversation),
            'key_interests' => self::extractKeyInterests($conversation),
            'questions_asked' => self::extractQuestions($conversation),
            'escalation_reason' => self::getEscalationReason($conversation)
        ];
        
        return $summary;
    }
    
    /**
     * Resumir conversación en texto legible
     */
    private static function summarizeConversation($conversation) {
        if (empty($conversation)) {
            return "No hay conversación registrada.";
        }
        
        $userMessages = array_filter($conversation, function($msg) {
            return $msg['sender'] === 'user';
        });
        
        $totalMessages = count($conversation);
        $userMessageCount = count($userMessages);
        
        $summary = "Conversación de {$totalMessages} mensajes ({$userMessageCount} del usuario). ";
        
        // Resumir temas principales
        $topics = [];
        foreach ($userMessages as $msg) {
            $message = strtolower($msg['message']);
            
            if (strpos($message, 'precio') !== false || strpos($message, 'costo') !== false) {
                $topics[] = 'precios';
            }
            if (strpos($message, 'como funciona') !== false || strpos($message, 'funciona') !== false) {
                $topics[] = 'funcionamiento';
            }
            if (strpos($message, 'seguro') !== false || strpos($message, 'garantia') !== false) {
                $topics[] = 'seguridad';
            }
            if (strpos($message, 'registro') !== false || strpos($message, 'empezar') !== false) {
                $topics[] = 'registro';
            }
            if (strpos($message, 'ganar') !== false || strpos($message, 'ganancia') !== false) {
                $topics[] = 'ganancias';
            }
        }
        
        if (!empty($topics)) {
            $uniqueTopics = array_unique($topics);
            $summary .= "Temas consultados: " . implode(', ', $uniqueTopics) . ".";
        }
        
        return $summary;
    }
    
    /**
     * Extraer intereses clave del prospecto
     */
    private static function extractKeyInterests($conversation) {
        $interests = [];
        
        foreach ($conversation as $msg) {
            if ($msg['sender'] !== 'user') continue;
            
            $message = strtolower($msg['message']);
            
            // Detectar nivel de interés
            if (strpos($message, 'me interesa') !== false || 
                strpos($message, 'quiero') !== false ||
                strpos($message, 'necesito') !== false) {
                $interests[] = 'Alto interés expresado';
            }
            
            // Detectar urgencia
            if (strpos($message, 'rapido') !== false || 
                strpos($message, 'pronto') !== false ||
                strpos($message, 'ya') !== false) {
                $interests[] = 'Urgencia temporal';
            }
            
            // Detectar capacidad económica
            if (strpos($message, 'cuanto') !== false || 
                strpos($message, 'precio') !== false) {
                $interests[] = 'Consulta sobre inversión';
            }
        }
        
        return array_unique($interests);
    }
    
    /**
     * Extraer preguntas específicas del usuario
     */
    private static function extractQuestions($conversation) {
        $questions = [];
        
        foreach ($conversation as $msg) {
            if ($msg['sender'] !== 'user') continue;
            
            $message = $msg['message'];
            
            // Detectar preguntas (contienen ?)
            if (strpos($message, '?') !== false) {
                $questions[] = $message;
            }
            
            // Detectar preguntas implícitas
            $questionWords = ['como', 'que', 'cuando', 'donde', 'por que', 'cuanto'];
            foreach ($questionWords as $word) {
                if (strpos(strtolower($message), $word) !== false) {
                    $questions[] = $message;
                    break;
                }
            }
        }
        
        // Limitar a las 5 preguntas más relevantes
        return array_slice(array_unique($questions), 0, 5);
    }
    
    /**
     * Determinar razón del escalamiento
     */
    private static function getEscalationReason($conversation) {
        $lastMessages = array_slice($conversation, -3); // Últimos 3 mensajes
        
        foreach ($lastMessages as $msg) {
            if ($msg['sender'] !== 'user') continue;
            
            $message = strtolower($msg['message']);
            
            if (strpos($message, 'humano') !== false || 
                strpos($message, 'persona') !== false ||
                strpos($message, 'asesor') !== false) {
                return 'Solicitó atención humana directamente';
            }
        }
        
        return 'Escalamiento automático por tipo de consulta';
    }
    
    /**
     * Generar mensaje de WhatsApp con contexto
     */
    public static function generateWhatsAppMessage($sessionId, $referrerName = null) {
        $summary = self::generateConversationSummary($sessionId);
        
        if (!$summary) {
            return "Hola! Un prospecto de la landing page quiere hablar contigo.";
        }
        
        $message = "🔔 *NUEVO PROSPECTO MIZTON*\n\n";
        
        if ($referrerName) {
            $message .= "Hola {$referrerName}! ";
        }
        
        $message .= "Un prospecto viene de tu enlace de referido:\n\n";
        
        // Información básica
        $message .= "📧 *Email:* {$summary['lead_info']['email']}\n";
        $message .= "🕒 *Hora:* " . date('d/m/Y H:i', strtotime($summary['lead_info']['created_at'])) . "\n\n";
        
        // Resumen de conversación
        $message .= "💬 *Resumen:* {$summary['conversation_summary']}\n\n";
        
        // Intereses clave
        if (!empty($summary['key_interests'])) {
            $message .= "🎯 *Intereses:* " . implode(', ', $summary['key_interests']) . "\n\n";
        }
        
        // Preguntas principales
        if (!empty($summary['questions_asked'])) {
            $message .= "❓ *Preguntas principales:*\n";
            foreach (array_slice($summary['questions_asked'], 0, 3) as $question) {
                $message .= "• " . $question . "\n";
            }
            $message .= "\n";
        }
        
        $message .= "🚀 *¡Listo para convertir!*";
        
        return $message;
    }
}

/**
 * Endpoint para obtener contexto de conversación
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'get_conversation_context':
            $sessionId = $input['session_id'] ?? '';
            $referrerName = $input['referrer_name'] ?? null;
            
            if (empty($sessionId)) {
                echo json_encode(['success' => false, 'message' => 'session_id requerido']);
                exit;
            }
            
            $whatsappMessage = ContextHandler::generateWhatsAppMessage($sessionId, $referrerName);
            $summary = ContextHandler::generateConversationSummary($sessionId);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'whatsapp_message' => $whatsappMessage,
                    'summary' => $summary
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
