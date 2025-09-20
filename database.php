<?php
/**
 * Configuración de Base de Datos para Landing Page
 * Utiliza las mismas credenciales que el panel según el entorno
 */

// Incluir configuración de entornos (bootstrap simplificado para landing)
require_once __DIR__ . '/bootstrap-landing.php';

/**
 * Obtiene información del usuario por código de referido
 */
function getUserByReferralCode($referralCode) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                idUser,
                nameUser,
                emailUser,
                userUser,
                founderUser,
                activeUser,
                regUser,
                celularUser,
                countryUser,
                landing_preference,
                waUser
            FROM tbluser 
            WHERE userUser = ? AND activeUser = 1
        ");
        
        $stmt->execute([$referralCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo usuario por referido: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene estadísticas del referido
 */
function getReferralStats($userId) {
    global $pdo;
    
    try {
        // Contar referidos directos activos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_referidos
            FROM tbluser 
            WHERE refUser = ? AND activeUser = 1
        ");
        $stmt->execute([$userId]);
        $totalReferidos = $stmt->fetchColumn();
        
        // Obtener información del tipo de fundador
        $founderTypes = [
            0 => 'Sin tipo',
            1 => 'Fundador Premium',
            2 => 'Fundador Gold', 
            3 => 'Fundador Silver',
            4 => 'Fundador Bronze',
            5 => 'Fundador Basic',
            6 => 'Miembro Estándar'
        ];
        
        $stmt = $pdo->prepare("SELECT founderUser FROM tbluser WHERE idUser = ?");
        $stmt->execute([$userId]);
        $founderType = $stmt->fetchColumn();
        
        return [
            'total_referidos' => $totalReferidos,
            'founder_type' => $founderType,
            'founder_name' => $founderTypes[$founderType] ?? 'Desconocido',
            'bonus_percentage' => getBonusPercentage($founderType)
        ];
        
    } catch (PDOException $e) {
        error_log("Error obteniendo estadísticas de referido: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el porcentaje de bono según el tipo de fundador
 * Basado en la lógica de bonos del sistema
 */
function getBonusPercentage($founderType) {
    $bonusMatrix = [
        0 => [0, 0, 0, 0, 0, 0],  // Sin tipo
        1 => [20, 5, 5, 5, 5, 5], // Premium
        2 => [20, 5, 5, 5, 5, 0], // Gold
        3 => [20, 5, 5, 5, 0, 0], // Silver
        4 => [20, 5, 5, 0, 0, 0], // Bronze
        5 => [20, 5, 4, 0, 0, 0], // Basic
        6 => [10, 6, 4, 0, 0, 0]  // Estándar
    ];
    
    return $bonusMatrix[$founderType] ?? [0, 0, 0, 0, 0, 0];
}

/**
 * Valida si un código de referido existe y está activo
 */
function validateReferralCode($referralCode) {
    if (empty($referralCode)) {
        return ['valid' => false, 'message' => 'Código de referido requerido'];
    }
    
    $user = getUserByReferralCode($referralCode);
    
    if (!$user) {
        return ['valid' => false, 'message' => 'Código de referido no válido o inactivo'];
    }
    
    $stats = getReferralStats($user['idUser']);
    
    return [
        'valid' => true,
        'user' => $user,
        'stats' => $stats,
        'message' => 'Código válido'
    ];
}

/**
 * Construye el número de WhatsApp completo con código de país
 */
function buildWhatsAppNumber($countryUser, $celularUser) {
    // Mapeo de códigos de país a códigos telefónicos
    $countryCodes = [
        'mx' => '52', 'us' => '1', 'ca' => '1', 'gt' => '502', 'sv' => '503',
        'hn' => '504', 'ni' => '505', 'cr' => '506', 'pa' => '507', 'pe' => '51',
        'ar' => '54', 'br' => '55', 'cl' => '56', 'co' => '57', 've' => '58',
        'ec' => '593', 'py' => '595', 'uy' => '598', 'bo' => '591', 'do' => '1',
        'cu' => '53', 'es' => '34'
    ];
    
    $phoneCode = $countryCodes[$countryUser] ?? '52'; // Default México
    
    // Limpiar el número de celular (solo dígitos)
    $cleanNumber = preg_replace('/[^0-9]/', '', $celularUser);
    
    return $phoneCode . $cleanNumber;
}
?>
