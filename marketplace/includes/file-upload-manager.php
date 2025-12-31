<?php
/**
 * Gestor de Upload de Archivos para Proyectos
 * Maneja subida de imágenes, documentos, videos y audio
 */

class FileUploadManager {
    
    private $uploadBasePath;
    private $uploadBaseUrl;
    private $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
        'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
        'audio' => ['mp3', 'wav', 'ogg', 'flac']
    ];
    private $maxFileSizes = [
        'image' => 10485760,      // 10 MB
        'document' => 52428800,   // 50 MB
        'video' => 524288000,     // 500 MB
        'audio' => 52428800       // 50 MB
    ];
    
    public function __construct() {
        $this->uploadBasePath = __DIR__ . '/../uploads/projects';
        $this->uploadBaseUrl = '/marketplace/uploads/projects';
        
        // Crear directorio base si no existe
        if (!is_dir($this->uploadBasePath)) {
            mkdir($this->uploadBasePath, 0755, true);
        }
    }
    
    /**
     * Subir archivo para un proyecto
     */
    public function uploadFile($file, $projectCode, $mediaType, $mediaCategory = 'general') {
        // Validar archivo
        $validation = $this->validateFile($file, $mediaType);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        // Crear estructura de directorios
        $projectDir = $this->uploadBasePath . '/' . $projectCode;
        $categoryDir = $projectDir . '/' . $mediaCategory;
        
        if (!is_dir($categoryDir)) {
            mkdir($categoryDir, 0755, true);
        }
        
        // Generar nombre único
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $this->generateUniqueFileName($file['name'], $extension);
        $filePath = $categoryDir . '/' . $fileName;
        $fileUrl = $this->uploadBaseUrl . '/' . $projectCode . '/' . $mediaCategory . '/' . $fileName;
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }
        
        // Optimizar imagen si es necesario
        if ($mediaType === 'image') {
            $this->optimizeImage($filePath, $extension);
        }
        
        // Preparar datos del archivo
        $fileData = [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'original_name' => $file['name']
        ];
        
        // Agregar metadata específica según tipo
        $metadata = $this->extractFileMetadata($filePath, $mediaType);
        
        return [
            'success' => true,
            'file_data' => $fileData,
            'metadata' => $metadata
        ];
    }
    
    /**
     * Validar archivo
     */
    private function validateFile($file, $mediaType) {
        // Verificar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Error al subir el archivo: ' . $file['error']];
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes[$mediaType])) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
        }
        
        // Verificar tamaño
        if ($file['size'] > $this->maxFileSizes[$mediaType]) {
            $maxSizeMB = $this->maxFileSizes[$mediaType] / 1048576;
            return ['valid' => false, 'error' => "El archivo excede el tamaño máximo de {$maxSizeMB}MB"];
        }
        
        // Verificar que sea un archivo real
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Archivo inválido'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generar nombre único para archivo
     */
    private function generateUniqueFileName($originalName, $extension) {
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = $this->sanitizeFileName($baseName);
        $timestamp = time();
        $random = substr(md5(uniqid(rand(), true)), 0, 8);
        
        return $baseName . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Sanitizar nombre de archivo
     */
    private function sanitizeFileName($fileName) {
        // Eliminar caracteres especiales
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);
        // Eliminar múltiples guiones bajos
        $fileName = preg_replace('/_+/', '_', $fileName);
        // Limitar longitud
        $fileName = substr($fileName, 0, 50);
        
        return $fileName;
    }
    
    /**
     * Optimizar imagen
     */
    private function optimizeImage($filePath, $extension) {
        // Cargar imagen según tipo
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                return; // No optimizar SVG u otros
        }
        
        if (!$image) {
            return;
        }
        
        // Obtener dimensiones
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Redimensionar si es muy grande (máximo 2000px en cualquier dimensión)
        $maxDimension = 2000;
        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $newWidth = $maxDimension;
                $newHeight = intval($height * ($maxDimension / $width));
            } else {
                $newHeight = $maxDimension;
                $newWidth = intval($width * ($maxDimension / $height));
            }
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preservar transparencia para PNG
            if ($extension === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Guardar imagen optimizada
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($resized, $filePath, 85);
                    break;
                case 'png':
                    imagepng($resized, $filePath, 8);
                    break;
                case 'gif':
                    imagegif($resized, $filePath);
                    break;
                case 'webp':
                    imagewebp($resized, $filePath, 85);
                    break;
            }
            
            imagedestroy($resized);
        }
        
        imagedestroy($image);
    }
    
    /**
     * Extraer metadata del archivo
     */
    private function extractFileMetadata($filePath, $mediaType) {
        $metadata = [];
        
        switch ($mediaType) {
            case 'image':
                $imageInfo = getimagesize($filePath);
                if ($imageInfo) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                    $metadata['aspect_ratio'] = round($imageInfo[0] / $imageInfo[1], 2);
                }
                break;
                
            case 'video':
                // Requiere FFmpeg instalado
                if (function_exists('shell_exec')) {
                    $output = shell_exec("ffprobe -v quiet -print_format json -show_format -show_streams " . escapeshellarg($filePath));
                    if ($output) {
                        $videoInfo = json_decode($output, true);
                        if (isset($videoInfo['format']['duration'])) {
                            $metadata['duration'] = floatval($videoInfo['format']['duration']);
                        }
                        if (isset($videoInfo['streams'][0])) {
                            $metadata['width'] = $videoInfo['streams'][0]['width'] ?? null;
                            $metadata['height'] = $videoInfo['streams'][0]['height'] ?? null;
                        }
                    }
                }
                break;
                
            case 'audio':
                // Requiere getID3 o similar
                // Por ahora solo guardamos info básica
                $metadata['format'] = pathinfo($filePath, PATHINFO_EXTENSION);
                break;
        }
        
        return $metadata;
    }
    
    /**
     * Eliminar archivo
     */
    public function deleteFile($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Crear thumbnail de imagen
     */
    public function createThumbnail($imagePath, $width = 300, $height = 300) {
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        // Cargar imagen
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($imagePath);
                break;
            case 'webp':
                $image = imagecreatefromwebp($imagePath);
                break;
            default:
                return null;
        }
        
        if (!$image) {
            return null;
        }
        
        // Obtener dimensiones originales
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        
        // Calcular dimensiones del thumbnail manteniendo aspecto
        $ratio = min($width / $origWidth, $height / $origHeight);
        $thumbWidth = intval($origWidth * $ratio);
        $thumbHeight = intval($origHeight * $ratio);
        
        // Crear thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preservar transparencia
        if ($extension === 'png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);
        
        // Guardar thumbnail
        $thumbPath = str_replace('.' . $extension, '_thumb.' . $extension, $imagePath);
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumbnail, $thumbPath, 85);
                break;
            case 'png':
                imagepng($thumbnail, $thumbPath, 8);
                break;
            case 'gif':
                imagegif($thumbnail, $thumbPath);
                break;
            case 'webp':
                imagewebp($thumbnail, $thumbPath, 85);
                break;
        }
        
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $thumbPath;
    }
    
    /**
     * Obtener tipos de archivo permitidos
     */
    public function getAllowedTypes($mediaType) {
        return $this->allowedTypes[$mediaType] ?? [];
    }
    
    /**
     * Obtener tamaño máximo permitido
     */
    public function getMaxFileSize($mediaType) {
        return $this->maxFileSizes[$mediaType] ?? 0;
    }
}
