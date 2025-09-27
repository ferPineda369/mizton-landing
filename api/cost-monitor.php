<?php
/**
 * Monitor de Costos de IA - Mizton
 * Rastrea y limita el uso de tokens para controlar gastos
 */

class CostMonitor {
    private $logFile;
    private $dailyLimit;
    private $costPerToken;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../data/ai_usage.json';
        $this->dailyLimit = 50000; // 50K tokens por día (~$100/mes máximo para alto volumen)
        $this->costPerToken = 0.000002; // $0.000002 por token (GPT-4o-mini)
        $this->ensureDataDirectory();
    }
    
    private function ensureDataDirectory() {
        $dataDir = dirname($this->logFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }
    
    /**
     * Verificar si se puede hacer una consulta sin exceder límites
     */
    public function canMakeQuery($estimatedTokens) {
        $today = date('Y-m-d');
        $usage = $this->getTodayUsage();
        
        $projectedTotal = $usage['tokens'] + $estimatedTokens;
        
        if ($projectedTotal > $this->dailyLimit) {
            error_log("Cost Monitor: Daily limit would be exceeded. Current: {$usage['tokens']}, Projected: {$projectedTotal}, Limit: {$this->dailyLimit}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Registrar uso de tokens
     */
    public function logUsage($tokens, $cost, $model, $query_type = 'chat') {
        $today = date('Y-m-d');
        $usage = $this->loadUsageData();
        
        if (!isset($usage[$today])) {
            $usage[$today] = [
                'tokens' => 0,
                'cost' => 0,
                'queries' => 0,
                'breakdown' => []
            ];
        }
        
        $usage[$today]['tokens'] += $tokens;
        $usage[$today]['cost'] += $cost;
        $usage[$today]['queries']++;
        
        if (!isset($usage[$today]['breakdown'][$query_type])) {
            $usage[$today]['breakdown'][$query_type] = [
                'tokens' => 0,
                'cost' => 0,
                'count' => 0
            ];
        }
        
        $usage[$today]['breakdown'][$query_type]['tokens'] += $tokens;
        $usage[$today]['breakdown'][$query_type]['cost'] += $cost;
        $usage[$today]['breakdown'][$query_type]['count']++;
        
        // Mantener solo últimos 30 días
        $this->cleanOldData($usage);
        
        file_put_contents($this->logFile, json_encode($usage, JSON_PRETTY_PRINT));
        
        error_log("Cost Monitor: Logged {$tokens} tokens, \${$cost}, Total today: {$usage[$today]['tokens']} tokens");
    }
    
    /**
     * Obtener uso de hoy
     */
    public function getTodayUsage() {
        $today = date('Y-m-d');
        $usage = $this->loadUsageData();
        
        return $usage[$today] ?? [
            'tokens' => 0,
            'cost' => 0,
            'queries' => 0,
            'breakdown' => []
        ];
    }
    
    /**
     * Obtener estadísticas de los últimos 7 días
     */
    public function getWeeklyStats() {
        $usage = $this->loadUsageData();
        $stats = [
            'total_tokens' => 0,
            'total_cost' => 0,
            'total_queries' => 0,
            'daily_average' => 0,
            'days' => []
        ];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dayData = $usage[$date] ?? ['tokens' => 0, 'cost' => 0, 'queries' => 0];
            
            $stats['total_tokens'] += $dayData['tokens'];
            $stats['total_cost'] += $dayData['cost'];
            $stats['total_queries'] += $dayData['queries'];
            $stats['days'][$date] = $dayData;
        }
        
        $stats['daily_average'] = $stats['total_tokens'] / 7;
        
        return $stats;
    }
    
    /**
     * Calcular costo estimado de tokens
     */
    public function calculateCost($tokens, $model = 'gpt-4o-mini') {
        $rates = [
            'gpt-4o-mini' => 0.000002,
            'gpt-3.5-turbo' => 0.000002,
            'gpt-4' => 0.00003
        ];
        
        return $tokens * ($rates[$model] ?? $this->costPerToken);
    }
    
    /**
     * Obtener límite de tokens recomendado según uso actual
     */
    public function getRecommendedTokenLimit() {
        $usage = $this->getTodayUsage();
        $remaining = $this->dailyLimit - $usage['tokens'];
        
        // Si quedan menos de 10K tokens, ser más conservador
        if ($remaining < 10000) {
            return min(2000, $remaining / 2);
        }
        
        // Uso normal
        return 4000;
    }
    
    private function loadUsageData() {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->logFile), true);
        return $data ?: [];
    }
    
    private function cleanOldData(&$usage) {
        $cutoff = date('Y-m-d', strtotime('-30 days'));
        
        foreach ($usage as $date => $data) {
            if ($date < $cutoff) {
                unset($usage[$date]);
            }
        }
    }
}

/**
 * Función helper para usar en otros archivos
 */
function checkAICostLimits($estimatedTokens) {
    $monitor = new CostMonitor();
    return $monitor->canMakeQuery($estimatedTokens);
}

function logAICost($tokens, $model, $query_type = 'chat') {
    $monitor = new CostMonitor();
    $cost = $monitor->calculateCost($tokens, $model);
    $monitor->logUsage($tokens, $cost, $model, $query_type);
}
?>
