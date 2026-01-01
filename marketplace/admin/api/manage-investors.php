<?php
/**
 * API para Gestión de Inversionistas
 * Permite agregar, actualizar y eliminar inversionistas de proyectos
 */

session_start();
require_once __DIR__ . '/../../config/marketplace-config.php';
require_once __DIR__ . '/../../includes/investor-access-functions.php';

header('Content-Type: application/json');

// Verificar autenticación de admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$adminId = $_SESSION['idUser'] ?? null;

try {
    switch ($action) {
        
        // ============================================================================
        // LISTAR INVERSIONISTAS DE UN PROYECTO
        // ============================================================================
        case 'list':
            $projectId = $_GET['project_id'] ?? null;
            
            if (!$projectId) {
                throw new Exception('ID de proyecto requerido');
            }
            
            $investors = getProjectInvestors($projectId, false);
            
            echo json_encode([
                'success' => true,
                'investors' => $investors,
                'total' => count($investors)
            ]);
            break;
        
        // ============================================================================
        // AGREGAR INVERSIONISTA
        // ============================================================================
        case 'add':
            $projectId = $_POST['project_id'] ?? null;
            $walletAddress = $_POST['wallet_address'] ?? null;
            $userId = $_POST['user_id'] ?? null;
            
            if (!$projectId || !$walletAddress) {
                throw new Exception('Proyecto y wallet address son requeridos');
            }
            
            // Validar formato de wallet
            if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $walletAddress)) {
                throw new Exception('Formato de wallet address inválido');
            }
            
            // Si se proporcionó user_id, verificar que exista y obtener su wallet
            if ($userId) {
                $db = getMarketplaceDB();
                $stmt = $db->prepare("SELECT idUser FROM tbluser WHERE idUser = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    throw new Exception('Usuario no encontrado');
                }
                
                // Obtener wallet del usuario desde tabla wallet
                $stmt = $db->prepare("SELECT address FROM wallet WHERE userId = ?");
                $stmt->execute([$userId]);
                $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($wallet && $wallet['address']) {
                    $walletAddress = $wallet['address'];
                }
            }
            
            $data = [
                'project_id' => $projectId,
                'user_id' => $userId,
                'wallet_address' => $walletAddress,
                'blockchain_network' => $_POST['blockchain_network'] ?? 'BSC',
                'token_amount' => $_POST['token_amount'] ?? 0,
                'investment_usd' => $_POST['investment_usd'] ?? 0,
                'investment_date' => $_POST['investment_date'] ?? date('Y-m-d'),
                'access_level' => $_POST['access_level'] ?? 'basic',
                'investor_type' => $userId ? 'mizton_user' : 'external_wallet',
                'notes' => $_POST['notes'] ?? null,
                'is_verified' => true,
                'verified_by' => $adminId,
                'created_by' => $adminId
            ];
            
            $investorId = addProjectInvestor($data);
            
            if (!$investorId) {
                throw new Exception('Error al agregar inversionista. Puede que ya exista.');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Inversionista agregado exitosamente',
                'investor_id' => $investorId
            ]);
            break;
        
        // ============================================================================
        // ACTUALIZAR INVERSIONISTA
        // ============================================================================
        case 'update':
            $investorId = $_POST['investor_id'] ?? null;
            
            if (!$investorId) {
                throw new Exception('ID de inversionista requerido');
            }
            
            $data = [];
            
            // Campos actualizables
            $fields = ['token_amount', 'investment_usd', 'investment_date', 'access_level', 
                      'kyc_status', 'aml_status', 'notes', 'is_active', 'is_verified'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }
            
            if (empty($data)) {
                throw new Exception('No hay datos para actualizar');
            }
            
            $result = updateProjectInvestor($investorId, $data, $adminId);
            
            if (!$result) {
                throw new Exception('Error al actualizar inversionista');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Inversionista actualizado exitosamente'
            ]);
            break;
        
        // ============================================================================
        // DESACTIVAR INVERSIONISTA
        // ============================================================================
        case 'deactivate':
            $investorId = $_POST['investor_id'] ?? null;
            
            if (!$investorId) {
                throw new Exception('ID de inversionista requerido');
            }
            
            $result = updateProjectInvestor($investorId, ['is_active' => false], $adminId);
            
            if (!$result) {
                throw new Exception('Error al desactivar inversionista');
            }
            
            logInvestorAction($investorId, 'deactivated', 'Inversionista desactivado por admin', $adminId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Inversionista desactivado exitosamente'
            ]);
            break;
        
        // ============================================================================
        // REACTIVAR INVERSIONISTA
        // ============================================================================
        case 'reactivate':
            $investorId = $_POST['investor_id'] ?? null;
            
            if (!$investorId) {
                throw new Exception('ID de inversionista requerido');
            }
            
            $result = updateProjectInvestor($investorId, ['is_active' => true], $adminId);
            
            if (!$result) {
                throw new Exception('Error al reactivar inversionista');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Inversionista reactivado exitosamente'
            ]);
            break;
        
        // ============================================================================
        // OBTENER INFORMACIÓN DE INVERSIONISTA
        // ============================================================================
        case 'get':
            $investorId = $_GET['investor_id'] ?? null;
            
            if (!$investorId) {
                throw new Exception('ID de inversionista requerido');
            }
            
            $db = getMarketplaceDB();
            $stmt = $db->prepare("SELECT * FROM vw_marketplace_investors WHERE id = ?");
            $stmt->execute([$investorId]);
            $investor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$investor) {
                throw new Exception('Inversionista no encontrado');
            }
            
            echo json_encode([
                'success' => true,
                'investor' => $investor
            ]);
            break;
        
        // ============================================================================
        // BUSCAR USUARIO MIZTON POR EMAIL O USERNAME
        // ============================================================================
        case 'search_user':
            $search = $_GET['search'] ?? '';
            
            if (strlen($search) < 3) {
                throw new Exception('Ingrese al menos 3 caracteres para buscar');
            }
            
            $db = getMarketplaceDB();
            $stmt = $db->prepare("
                SELECT u.idUser as id, u.userUser as username, u.emailUser as email, w.address as wallet_address 
                FROM tbluser u
                LEFT JOIN wallet w ON u.idUser = w.userId
                WHERE (u.userUser LIKE ? OR u.emailUser LIKE ?) 
                AND u.activeUser = 1
                LIMIT 10
            ");
            $searchTerm = "%$search%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ]);
            break;
        
        // ============================================================================
        // OBTENER NIVELES DE ACCESO
        // ============================================================================
        case 'get_access_levels':
            $levels = getAllAccessLevels();
            
            echo json_encode([
                'success' => true,
                'levels' => $levels
            ]);
            break;
        
        // ============================================================================
        // OBTENER LOGS DE INVERSIONISTA
        // ============================================================================
        case 'get_logs':
            $investorId = $_GET['investor_id'] ?? null;
            
            if (!$investorId) {
                throw new Exception('ID de inversionista requerido');
            }
            
            $db = getMarketplaceDB();
            $stmt = $db->prepare("
                SELECT l.*, u.userUser as performed_by_name
                FROM tbl_marketplace_investor_logs l
                LEFT JOIN tbluser u ON l.performed_by = u.idUser
                WHERE l.investor_id = ?
                ORDER BY l.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$investorId]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar JSON
            foreach ($logs as &$log) {
                if ($log['old_values']) {
                    $log['old_values'] = json_decode($log['old_values'], true);
                }
                if ($log['new_values']) {
                    $log['new_values'] = json_decode($log['new_values'], true);
                }
            }
            
            echo json_encode([
                'success' => true,
                'logs' => $logs
            ]);
            break;
        
        // ============================================================================
        // ESTADÍSTICAS DE INVERSIONISTAS
        // ============================================================================
        case 'stats':
            $projectId = $_GET['project_id'] ?? null;
            
            if (!$projectId) {
                throw new Exception('ID de proyecto requerido');
            }
            
            $db = getMarketplaceDB();
            
            // Estadísticas generales
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_investors,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_investors,
                    SUM(investment_usd) as total_investment,
                    AVG(investment_usd) as avg_investment,
                    COUNT(CASE WHEN kyc_status = 'approved' THEN 1 END) as kyc_approved,
                    COUNT(CASE WHEN investor_type = 'mizton_user' THEN 1 END) as mizton_users,
                    COUNT(CASE WHEN investor_type = 'external_wallet' THEN 1 END) as external_wallets
                FROM tbl_marketplace_project_investors
                WHERE project_id = ?
            ");
            $stmt->execute([$projectId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Por nivel de acceso
            $stmt = $db->prepare("
                SELECT access_level, COUNT(*) as count, SUM(investment_usd) as total_investment
                FROM tbl_marketplace_project_investors
                WHERE project_id = ? AND is_active = 1
                GROUP BY access_level
            ");
            $stmt->execute([$projectId]);
            $byLevel = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'by_level' => $byLevel
            ]);
            break;
        
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
