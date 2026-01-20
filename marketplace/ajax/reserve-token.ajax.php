<?php
/**
 * AJAX Handler para Crear Reserva de Tokens del Marketplace
 */

session_start();
require_once __DIR__ . '/../config/marketplace-config.php';

header('Content-Type: application/json');

try {
    // Verificar autenticación
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autenticado');
    }
    
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Token CSRF inválido');
    }
    
    // Validar datos requeridos
    $projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
    $userId = $_SESSION['user_id'];
    $tokenAmount = filter_input(INPUT_POST, 'token_amount', FILTER_VALIDATE_FLOAT);
    $walletAddress = trim($_POST['wallet_address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    $userNotes = trim($_POST['user_notes'] ?? '');
    
    // Validaciones
    if (!$projectId) {
        throw new Exception('ID de proyecto inválido');
    }
    
    if (!$tokenAmount || $tokenAmount < 1) {
        throw new Exception('Cantidad de tokens inválida (mínimo 1)');
    }
    
    // Validar formato de wallet solo si se proporciona
    if (!empty($walletAddress) && !preg_match('/^0x[a-fA-F0-9]{40}$/', $walletAddress)) {
        throw new Exception('Formato de wallet inválido');
    }
    
    // Si no se proporciona wallet, usar NULL para indicar custodia por Mizton
    if (empty($walletAddress)) {
        $walletAddress = null;
    }
    
    if (!in_array($paymentMethod, ['crypto', 'bank_transfer', 'paypal', 'stripe', 'other'])) {
        throw new Exception('Método de pago inválido');
    }
    
    // Obtener conexión a base de datos
    $db = getMarketplaceDB();
    
    // Obtener información del proyecto
    $stmt = $db->prepare("
        SELECT id, project_code, token_price_usd, total_supply, circulating_supply
        FROM tbl_marketplace_projects
        WHERE id = ? AND is_active = 1
    ");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        throw new Exception('Proyecto no encontrado o inactivo');
    }
    
    // Verificar disponibilidad de tokens
    $available = $project['total_supply'] - $project['circulating_supply'];
    if ($tokenAmount > $available) {
        throw new Exception('No hay suficientes tokens disponibles');
    }
    
    // Calcular total
    $tokenPriceUsd = $project['token_price_usd'];
    $totalUsd = $tokenAmount * $tokenPriceUsd;
    
    // Crear reserva
    $stmt = $db->prepare("
        INSERT INTO tbl_marketplace_token_reserves (
            project_id,
            project_code,
            user_id,
            wallet_address,
            token_amount,
            token_price_usd,
            total_usd,
            payment_method,
            user_notes,
            status,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->execute([
        $projectId,
        $project['project_code'],
        $userId,
        $walletAddress,
        $tokenAmount,
        $tokenPriceUsd,
        $totalUsd,
        $paymentMethod,
        $userNotes
    ]);
    
    $reserveId = $db->lastInsertId();

    // Log de auditoría
    error_log(sprintf(
        "[MARKETPLACE RESERVE] User %d created reserve #%d for project %d: %s tokens at $%s USD each (Total: $%s USD)",
        $userId,
        $reserveId,
        $projectId,
        $tokenAmount,
        $tokenPriceUsd,
        $totalUsd
    ));
    
    echo json_encode([
        'success' => true,
        'reserve_id' => $reserveId,
        'message' => 'Reserva creada exitosamente',
        'data' => [
            'token_amount' => $tokenAmount,
            'total_usd' => $totalUsd,
            'next_step' => 'upload_proof'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
