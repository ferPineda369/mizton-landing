<?php
/**
 * API para obtener información de referidos
 * Endpoint: /landing/api/referral-info.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database.php';

try {
    $referralCode = '';
    
    // Obtener código de referido desde GET o POST
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $referralCode = $_GET['ref'] ?? '';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $referralCode = $input['ref'] ?? '';
    }
    
    $referralCode = trim($referralCode);
    
    if (empty($referralCode)) {
        echo json_encode([
            'success' => false,
            'message' => 'Código de referido requerido',
            'data' => null
        ]);
        exit;
    }
    
    // Validar código de referido
    $validation = validateReferralCode($referralCode);
    
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => $validation['message'],
            'data' => null
        ]);
        exit;
    }
    
    // Determinar número de WhatsApp según preferencia del usuario
    $whatsappNumber = DEFAULT_WHATSAPP; // Número predeterminado desde .env
    $landingPreference = $validation['user']['landing_preference'] ?? 0;
    $hasWhatsApp = ($validation['user']['waUser'] ?? 0) == 1;
    
    if ($landingPreference == 1 && $hasWhatsApp && !empty($validation['user']['celularUser']) && !empty($validation['user']['countryUser'])) {
        // Construir número completo usando countryUser + celularUser
        $whatsappNumber = buildWhatsAppNumber($validation['user']['countryUser'], $validation['user']['celularUser']);
    }
    
    // Preparar respuesta con información del referido
    $response = [
        'success' => true,
        'message' => 'Información obtenida correctamente',
        'data' => [
            'referral_code' => $referralCode,
            'referrer' => [
                'name' => $validation['user']['nameUser'],
                'email' => $validation['user']['emailUser'],
                'founder_type' => $validation['stats']['founder_name'],
                'member_since' => date('Y-m-d', $validation['user']['regUser'])
            ],
            'stats' => [
                'total_referrals' => $validation['stats']['total_referidos'],
                'bonus_levels' => $validation['stats']['bonus_percentage'],
                'first_level_bonus' => $validation['stats']['bonus_percentage'][0] . '%'
            ],
            'benefits' => [
                'guaranteed_recovery' => '100%',
                'additional_bonus' => '15%',
                'monthly_dividends' => true,
                'vesting_period' => '360 días'
            ],
            'contact' => [
                'whatsapp_number' => $whatsappNumber,
                'is_personal' => ($landingPreference == 1)
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en referral-info.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'data' => null
    ]);
}
?>
