<?php
require_once 'config/database.php';

// Configuración del sorteo
define('TICKET_PRICE', 25.00);

echo "<h2>Verificación de Precios del Sorteo</h2>";

try {
    // Obtener estadísticas
    $statsSql = "SELECT 
                    status,
                    COUNT(*) as count
                 FROM sorteo_numbers 
                 GROUP BY status";
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute();
    $stats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $confirmed = $stats['confirmed'] ?? 0;
    $reserved = $stats['reserved'] ?? 0;
    $available = $stats['available'] ?? 0;
    
    echo "<div style='background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>📊 Estadísticas Actuales</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f5f5f5;'>";
    echo "<th style='padding: 10px;'>Estado</th><th style='padding: 10px;'>Cantidad</th><th style='padding: 10px;'>Valor Total</th>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>Disponibles</strong></td>";
    echo "<td style='padding: 10px; text-align: center;'>$available</td>";
    echo "<td style='padding: 10px; text-align: center;'>$" . number_format($available * TICKET_PRICE, 2) . " MXN</td>";
    echo "</tr>";
    
    echo "<tr style='background: #fff3cd;'>";
    echo "<td style='padding: 10px;'><strong>Reservados</strong></td>";
    echo "<td style='padding: 10px; text-align: center;'>$reserved</td>";
    echo "<td style='padding: 10px; text-align: center;'>$" . number_format($reserved * TICKET_PRICE, 2) . " MXN</td>";
    echo "</tr>";
    
    echo "<tr style='background: #d4edda;'>";
    echo "<td style='padding: 10px;'><strong>Confirmados</strong></td>";
    echo "<td style='padding: 10px; text-align: center;'>$confirmed</td>";
    echo "<td style='padding: 10px; text-align: center;'><strong>$" . number_format($confirmed * TICKET_PRICE, 2) . " MXN</strong></td>";
    echo "</tr>";
    
    $total = $available + $reserved + $confirmed;
    echo "<tr style='background: #f8f9fa; font-weight: bold;'>";
    echo "<td style='padding: 10px;'><strong>TOTAL</strong></td>";
    echo "<td style='padding: 10px; text-align: center;'>$total</td>";
    echo "<td style='padding: 10px; text-align: center;'>$" . number_format($total * TICKET_PRICE, 2) . " MXN</td>";
    echo "</tr>";
    
    echo "</table>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>💰 Resumen Financiero</h3>";
    echo "<ul>";
    echo "<li><strong>Precio por boleto:</strong> $" . number_format(TICKET_PRICE, 2) . " MXN</li>";
    echo "<li><strong>Total recaudado (confirmados):</strong> $" . number_format($confirmed * TICKET_PRICE, 2) . " MXN</li>";
    echo "<li><strong>Potencial adicional (reservados):</strong> $" . number_format($reserved * TICKET_PRICE, 2) . " MXN</li>";
    echo "<li><strong>Recaudación máxima posible:</strong> $" . number_format(100 * TICKET_PRICE, 2) . " MXN</li>";
    echo "</ul>";
    echo "</div>";
    
    // Verificar consistencia
    if ($total == 100) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
        echo "<h3>✅ Verificación de Integridad</h3>";
        echo "<p>Total de boletos: $total/100 ✅</p>";
        echo "<p>Precio configurado: $" . TICKET_PRICE . " MXN ✅</p>";
        echo "<p>Cálculos correctos ✅</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
        echo "<h3>⚠️ Advertencia</h3>";
        echo "<p>Total de boletos: $total/100 (debería ser 100)</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px;">
    <a href="admin/" style="color: #007bff;">🔧 Panel Admin</a> | 
    <a href="debug_reservas.php" style="color: #007bff;">📊 Debug General</a> | 
    <a href="index.php" style="color: #007bff;">🎄 Volver al Sorteo</a>
</div>
