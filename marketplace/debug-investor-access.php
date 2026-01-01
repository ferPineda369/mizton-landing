<?php
/**
 * Script de Diagnóstico - Control de Acceso de Inversionistas
 * Temporal para debugging
 */

require_once __DIR__ . '/config/marketplace-config.php';
require_once __DIR__ . '/includes/marketplace-functions.php';
require_once __DIR__ . '/includes/investor-access-functions.php';

// Forzar display de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Control de Acceso</h1>";
echo "<pre>";

// 1. Verificar sesión
echo "=== SESIÓN ===\n";
echo "Session ID: " . session_id() . "\n";
echo "idUser en sesión: " . ($_SESSION['idUser'] ?? 'NO DEFINIDO') . "\n";
echo "Todas las variables de sesión:\n";
print_r($_SESSION);

// 2. Parámetros de prueba
$projectId = 10;
$userId = $_SESSION['idUser'] ?? 14; // Usar 14 como fallback para pruebas
$documentId = 16; // "Información para Inversores"

echo "\n=== PARÁMETROS DE PRUEBA ===\n";
echo "Project ID: $projectId\n";
echo "User ID: $userId\n";
echo "Document ID: $documentId\n";

// 3. Obtener wallet del usuario
echo "\n=== WALLET DEL USUARIO ===\n";
$walletAddress = getUserWalletAddress($userId);
echo "Wallet Address: " . ($walletAddress ?: 'NO ENCONTRADA') . "\n";

// 4. Verificar si es inversionista
echo "\n=== VERIFICACIÓN DE INVERSIONISTA ===\n";
$investor = isProjectInvestor($projectId, $userId, $walletAddress);
if ($investor) {
    echo "✅ ES INVERSIONISTA\n";
    echo "Nivel de acceso: {$investor['access_level']}\n";
    echo "Inversión USD: {$investor['investment_usd']}\n";
    echo "Activo: " . ($investor['is_active'] ? 'SÍ' : 'NO') . "\n";
    echo "Verificado: " . ($investor['is_verified'] ? 'SÍ' : 'NO') . "\n";
    echo "\nDatos completos del inversionista:\n";
    print_r($investor);
} else {
    echo "❌ NO ES INVERSIONISTA\n";
}

// 5. Verificar acceso al documento
echo "\n=== VERIFICACIÓN DE ACCESO AL DOCUMENTO ===\n";
$access = checkDocumentAccess($documentId, $userId, $walletAddress);
echo "Acceso permitido: " . ($access['access'] ? '✅ SÍ' : '❌ NO') . "\n";
echo "Razón: {$access['reason']}\n";
echo "\nDatos completos de verificación:\n";
print_r($access);

// 6. Obtener todos los documentos accesibles
echo "\n=== DOCUMENTOS ACCESIBLES ===\n";
$documents = getAccessibleDocuments($projectId, $userId, $walletAddress);
echo "Total de documentos: " . count($documents) . "\n\n";
foreach ($documents as $doc) {
    echo "- {$doc['document_name']}\n";
    echo "  Público: " . ($doc['is_public'] ? 'SÍ' : 'NO') . "\n";
    echo "  Nivel requerido: {$doc['required_access_level']}\n";
    echo "  Tiene acceso: " . ($doc['has_access'] ? '✅ SÍ' : '❌ NO') . "\n";
    if (!$doc['has_access']) {
        echo "  Razón: {$doc['access_reason']}\n";
    }
    echo "\n";
}

// 7. Consulta directa a la BD
echo "\n=== CONSULTA DIRECTA A BD ===\n";
$db = getMarketplaceDB();

// Verificar inversionista en BD
$stmt = $db->prepare("
    SELECT * FROM tbl_marketplace_project_investors 
    WHERE project_id = ? AND user_id = ?
");
$stmt->execute([$projectId, $userId]);
$dbInvestor = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Inversionista en BD:\n";
print_r($dbInvestor);

// Verificar documento en BD
$stmt = $db->prepare("
    SELECT * FROM tbl_marketplace_documents 
    WHERE id = ?
");
$stmt->execute([$documentId]);
$dbDocument = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nDocumento en BD:\n";
print_r($dbDocument);

echo "</pre>";

echo "<hr>";
echo "<h2>Conclusión</h2>";
if ($access['access']) {
    echo "<p style='color: green; font-size: 20px;'>✅ El usuario DEBERÍA tener acceso al documento</p>";
} else {
    echo "<p style='color: red; font-size: 20px;'>❌ El usuario NO tiene acceso al documento</p>";
    echo "<p><strong>Razón:</strong> {$access['reason']}</p>";
}
?>
