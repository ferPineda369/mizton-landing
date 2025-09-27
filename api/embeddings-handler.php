<?php
/**
 * Sistema de Embeddings para manejo inteligente de conocimiento extenso
 * Permite buscar información relevante sin enviar todo el contenido
 */

class EmbeddingsHandler {
    private $config;
    private $vectorsFile;
    
    public function __construct() {
        $this->config = AIConfig::getOpenAIConfig();
        $this->vectorsFile = __DIR__ . '/../data/knowledge_vectors.json';
        $this->ensureDataDirectory();
    }
    
    private function ensureDataDirectory() {
        $dataDir = dirname($this->vectorsFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }
    
    /**
     * Dividir conocimiento en chunks y crear embeddings
     */
    public function createKnowledgeEmbeddings() {
        $knowledge = AIConfig::getKnowledgeBase(50000); // Sin límite para procesar todo
        
        // Dividir en chunks por secciones
        $chunks = $this->splitIntoChunks($knowledge);
        $vectors = [];
        
        foreach ($chunks as $index => $chunk) {
            $embedding = $this->createEmbedding($chunk['content']);
            if ($embedding) {
                $vectors[] = [
                    'id' => $index,
                    'title' => $chunk['title'],
                    'content' => $chunk['content'],
                    'embedding' => $embedding,
                    'tokens' => $this->estimateTokens($chunk['content'])
                ];
                
                // Pausa para evitar rate limiting
                usleep(200000); // 200ms
            }
        }
        
        // Guardar vectores
        file_put_contents($this->vectorsFile, json_encode($vectors, JSON_PRETTY_PRINT));
        error_log("Embeddings: Created " . count($vectors) . " knowledge vectors");
        
        return count($vectors);
    }
    
    /**
     * Buscar información relevante usando similitud semántica
     */
    public function findRelevantKnowledge($query, $maxTokens = 6000) {
        if (!file_exists($this->vectorsFile)) {
            error_log("Embeddings: Vector file not found, creating embeddings...");
            $this->createKnowledgeEmbeddings();
        }
        
        $vectors = json_decode(file_get_contents($this->vectorsFile), true);
        if (!$vectors) {
            return AIConfig::getKnowledgeBase(6000); // Fallback
        }
        
        // Crear embedding de la consulta
        $queryEmbedding = $this->createEmbedding($query);
        if (!$queryEmbedding) {
            return AIConfig::getKnowledgeBase(6000); // Fallback
        }
        
        // Calcular similitudes
        $similarities = [];
        foreach ($vectors as $vector) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $vector['embedding']);
            $similarities[] = [
                'similarity' => $similarity,
                'content' => $vector['content'],
                'title' => $vector['title'],
                'tokens' => $vector['tokens']
            ];
        }
        
        // Ordenar por similitud
        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        // Seleccionar chunks más relevantes dentro del límite de tokens
        $selectedContent = [];
        $totalTokens = 0;
        
        foreach ($similarities as $item) {
            if ($totalTokens + $item['tokens'] <= $maxTokens) {
                $selectedContent[] = "## " . $item['title'] . "\n" . $item['content'];
                $totalTokens += $item['tokens'];
            }
        }
        
        $result = implode("\n\n", $selectedContent);
        error_log("Embeddings: Selected " . count($selectedContent) . " chunks, ~{$totalTokens} tokens");
        
        return $result ?: AIConfig::getKnowledgeBase(6000);
    }
    
    private function splitIntoChunks($content) {
        $chunks = [];
        
        // Dividir por secciones de Markdown
        $sections = preg_split('/^## /m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        foreach ($sections as $section) {
            if (trim($section)) {
                $lines = explode("\n", $section);
                $title = trim($lines[0] ?? 'Información General');
                $content = implode("\n", array_slice($lines, 1));
                
                if (strlen($content) > 100) { // Solo chunks con contenido sustancial
                    $chunks[] = [
                        'title' => $title,
                        'content' => trim($content)
                    ];
                }
            }
        }
        
        return $chunks;
    }
    
    private function createEmbedding($text) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/embeddings');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'text-embedding-3-small',
            'input' => $text
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config['api_key']
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['data'][0]['embedding'] ?? null;
        }
        
        error_log("Embeddings API Error: HTTP {$httpCode} - {$response}");
        return null;
    }
    
    private function cosineSimilarity($a, $b) {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }
        
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
    
    private function estimateTokens($text) {
        return ceil(strlen($text) / 4); // Aproximación: 4 chars = 1 token
    }
}
?>
