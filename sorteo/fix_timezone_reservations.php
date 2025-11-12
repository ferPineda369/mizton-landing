<?php
require_once 'config/database.php';

// Establecer zona horaria para México
date_default_timezone_set('America/Mexico_City');

echo "<h2>Corrección de Reservas con Problemas de Zona Horaria</h2>";

try {
    // Mostrar hora actual del servidor
    echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Hora actual del servidor:</strong> " . date('Y-m-d H:i:s') . " (Mexico City)<br>";
    echo "<strong>Timestamp actual:</strong> " . time() . "<br>";
    echo "<strong>UTC actual:</strong> " . gmdate('Y-m-d H:i:s') . "<br>";
    echo "</div>";
    
    // Verificar reservas actuales
    echo "<h3>Reservas Actuales:</h3>";
    $sql = "SELECT 
                number_value,
                participant_name,
                participant_movil,
                reserved_at,
                reservation_expires_at,
                TIMESTAMPDIFF(MINUTE, reserved_at, reservation_expires_at) as minutes_diff,
                CASE 
                    WHEN reservation_expires_at < NOW() THEN 'EXPIRADA'
                    ELSE 'ACTIVA'
                END as status_check
            FROM sorteo_numbers 
            WHERE status = 'reserved' 
            ORDER BY reserved_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($reservations) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'>";
        echo "<th>Número</th><th>Participante</th><th>Celular</th><th>Reservado</th><th>Expira</th><th>Diferencia (min)</th><th>Estado</th><th>Acción</th>";
        echo "</tr>";
        
        foreach ($reservations as $res) {
            $bgColor = $res['minutes_diff'] > 20 ? 'background: #ffebee;' : 'background: #e8f5e8;';
            $statusColor = $res['status_check'] === 'EXPIRADA' ? 'color: red;' : 'color: green;';
            
            echo "<tr style='$bgColor'>";
            echo "<td>" . $res['number_value'] . "</td>";
            echo "<td>" . ($res['participant_name'] ?? 'NULL') . "</td>";
            echo "<td>" . ($res['participant_movil'] ?? 'NULL') . "</td>";
            echo "<td>" . $res['reserved_at'] . "</td>";
            echo "<td>" . $res['reservation_expires_at'] . "</td>";
            echo "<td style='font-weight: bold;'>" . $res['minutes_diff'] . "</td>";
            echo "<td style='$statusColor font-weight: bold;'>" . $res['status_check'] . "</td>";
            echo "<td>";
            if ($res['minutes_diff'] > 20) {
                echo "<span style='color: orange;'>⚠️ TIEMPO INCORRECTO</span>";
            } else {
                echo "<span style='color: green;'>✅ CORRECTO</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar reservas con problemas
        $problematicas = array_filter($reservations, function($res) {
            return $res['minutes_diff'] > 20;
        });
        
        if (count($problematicas) > 0) {
            echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border: 1px solid #ffeaa7;'>";
            echo "<h4>⚠️ Se encontraron " . count($problematicas) . " reservas con tiempos incorrectos</h4>";
            echo "<p>Estas reservas tienen más de 20 minutos de diferencia, probablemente debido al problema de zona horaria.</p>";
            
            echo "<form method='POST' style='margin-top: 10px;'>";
            echo "<button type='submit' name='fix_reservations' class='btn' style='background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Corregir Reservas Problemáticas</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border: 1px solid #c3e6cb;'>";
            echo "<h4>✅ Todas las reservas tienen tiempos correctos</h4>";
            echo "</div>";
        }
        
    } else {
        echo "<p>No hay reservas activas en este momento.</p>";
    }
    
    // Procesar corrección si se solicita
    if (isset($_POST['fix_reservations'])) {
        echo "<h3>Corrigiendo Reservas Problemáticas:</h3>";
        
        // Corregir reservas con más de 20 minutos
        $fixSql = "UPDATE sorteo_numbers 
                   SET reservation_expires_at = DATE_ADD(reserved_at, INTERVAL 15 MINUTE)
                   WHERE status = 'reserved' 
                   AND TIMESTAMPDIFF(MINUTE, reserved_at, reservation_expires_at) > 20";
        
        $fixStmt = $pdo->prepare($fixSql);
        $result = $fixStmt->execute();
        
        if ($result) {
            $affectedRows = $fixStmt->rowCount();
            echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border: 1px solid #c3e6cb;'>";
            echo "<h4>✅ Corrección Completada</h4>";
            echo "<p>Se corrigieron <strong>$affectedRows</strong> reservas.</p>";
            echo "<p>Ahora todas las reservas expiran exactamente 15 minutos después de ser creadas.</p>";
            echo "</div>";
            
            // Mostrar reservas corregidas
            echo "<p><a href='" . $_SERVER['PHP_SELF'] . "' style='color: #007bff;'>🔄 Recargar para ver cambios</a></p>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border: 1px solid #f5c6cb;'>";
            echo "<h4>❌ Error en la Corrección</h4>";
            echo "<p>No se pudieron corregir las reservas.</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px;">
    <a href="debug_reservas.php" style="color: #007bff;">📊 Ver Debug General</a> | 
    <a href="admin/" style="color: #007bff;">🔧 Panel Admin</a> | 
    <a href="index.php" style="color: #007bff;">🎄 Volver al Sorteo</a>
</div>
