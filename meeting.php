<?php
// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manejo de códigos de referido - soporte para URLs limpias y parámetros tradicionales
if (isset($_GET['ref'])) {
    $referido = preg_replace('/[^a-z0-9]/', '', strtolower($_GET['ref']));
    
    // Validar que el código tenga exactamente 6 caracteres alfanuméricos
    if (strlen($referido) === 6 && ctype_alnum($referido)) {
        $_SESSION['referido'] = $referido;
        
        // Debug: verificar que la variable se está guardando
        $logFile = __DIR__ . '/meeting_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        if (is_writable(dirname($logFile))) {
            file_put_contents($logFile, "[$timestamp] MEETING DEBUG: Referido válido guardado: " . $_SESSION['referido'] . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] MEETING DEBUG: Session ID: " . session_id() . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] MEETING DEBUG: URL original: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
        }
    }
}

include 'config.php';

// Configuración de base de datos usando la configuración del blog
$pdo = null;
$zoomVideo = null;

try {
    // Usar la configuración de base de datos del blog
    require_once __DIR__ . '/news/config/database-blog.php';
    
    // La variable $pdo ya está definida en database-blog.php
    // Obtener información del video de Zoom desde la base de datos
    $stmt = $pdo->prepare("
        SELECT * FROM tbl_zoom_daily_video 
        WHERE is_active = 1 
        ORDER BY date_created DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $zoomVideo = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error de base de datos en meeting.php: " . $e->getMessage());
    $zoomVideo = null;
}

// Valores por defecto si no hay video configurado
if (!$zoomVideo) {
    $zoomVideo = [
        'title' => 'Presentación de Oportunidad de Negocio Mizton',
        'description' => 'Descubre cómo la tokenización de activos está revolucionando la economía mundial',
        'zoom_url' => '#',
        'date_created' => date('Y-m-d')
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentación de Oportunidad - Mizton</title>
    <meta name="description" content="Únete a nuestra presentación exclusiva sobre la tokenización de activos del mundo real. Descubre la narrativa que está cambiando la economía mundial.">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/meeting.php">
    <meta property="og:title" content="Presentación de Oportunidad - Mizton">
    <meta property="og:description" content="Únete a nuestra presentación exclusiva sobre la tokenización de activos del mundo real. Descubre la narrativa que está cambiando la economía mundial.">
    <meta property="og:image" content="https://mizton.cat/social-preview.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Mizton">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://mizton.cat/meeting.php">
    <meta property="twitter:title" content="Presentación de Oportunidad - Mizton">
    <meta property="twitter:description" content="Únete a nuestra presentación exclusiva sobre la tokenización de activos del mundo real. Descubre la narrativa que está cambiando la economía mundial.">
    <meta property="twitter:image" content="https://mizton.cat/social-preview.jpg">
    
    <!-- WhatsApp -->
    <meta property="og:locale" content="es_ES">
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Meta Pixel Code with AdBlocker Bypass -->
    <script src="fb-proxy.js"></script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=684765634652448&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    
    <style>
        .meeting-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .meeting-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .meeting-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .meeting-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .meeting-info h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }
        
        .meeting-info .highlight {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .meeting-description {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .tokenization-intro {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .tokenization-intro h3 {
            color: #FFD700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .tokenization-intro p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            margin-bottom: 0;
        }
        
        .meeting-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .detail-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .detail-item i {
            font-size: 2rem;
            color: #FFD700;
            margin-bottom: 0.5rem;
        }
        
        .detail-item h4 {
            color: white;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .detail-item p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin: 0;
        }
        
        .meeting-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .meeting-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #FFD700);
        }
        
        .zoom-preview {
            background: linear-gradient(135deg, #2D8CFF 0%, #1E6FBA 100%);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .zoom-preview::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .zoom-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .zoom-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .zoom-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .live-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #FF4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            animation: blink 2s infinite;
            z-index: 3;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.7; }
        }
        
        .meeting-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-zoom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-zoom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-share {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-share:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .back-link:hover {
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .meeting-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .meeting-info h1 {
                font-size: 2.5rem;
            }
            
            .meeting-card {
                padding: 2rem;
            }
            
            .meeting-actions {
                grid-template-columns: 1fr;
            }
            
            .meeting-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="meeting-hero">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
        
        <div class="meeting-container">
            <div class="meeting-content">
                <div class="meeting-info">
                    <h1>
                        Descubre la <span class="highlight">Narrativa</span> que está 
                        <span class="highlight">Cambiando</span> la Economía Mundial
                    </h1>
                    
                    <p class="meeting-description">
                        <?php echo htmlspecialchars($zoomVideo['description']); ?>
                    </p>
                    
                    <div class="tokenization-intro">
                        <h3><i class="fas fa-coins"></i> Tokenización de Activos del Mundo Real</h3>
                        <p>
                            Estás a punto de conocer la narrativa que <strong>YA está cambiando la economía mundial</strong>. 
                            Nos referimos a la narrativa de la <strong>Tokenización de Activos del Mundo Real en la Blockchain</strong>. 
                            Una tecnología en la que puedes participar de manera <strong>MUY simple</strong>.
                        </p>
                    </div>
                    
                    <div class="meeting-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <h4>Fecha</h4>
                            <p><?php echo date('d/m/Y', strtotime($zoomVideo['date_created'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <h4>Horario</h4>
                            <p>Lunes a Viernes</p>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <h4>Modalidad</h4>
                            <p>Presentación en Vivo</p>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-globe"></i>
                            <h4>Acceso</h4>
                            <p>Completamente Gratuito</p>
                        </div>
                    </div>
                </div>
                
                <div class="meeting-card">
                    <div class="zoom-preview">
                        <div class="live-badge">
                            <i class="fas fa-circle"></i> EN VIVO
                        </div>
                        <div class="zoom-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="zoom-title"><?php echo htmlspecialchars($zoomVideo['title']); ?></h3>
                        <p class="zoom-subtitle">Presentación Exclusiva de Oportunidad de Negocio</p>
                    </div>
                    
                    <div class="meeting-actions">
                        <a href="#" class="btn-zoom" id="joinZoomBtn" data-zoom-url="<?php echo htmlspecialchars($zoomVideo['zoom_url']); ?>">
                            <i class="fas fa-play"></i>
                            Unirse Ahora
                        </a>
                        <button class="btn-share" id="shareBtn">
                            <i class="fas fa-share-alt"></i>
                            Compartir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejar clic en botón "Unirse a Zoom"
            document.getElementById('joinZoomBtn').addEventListener('click', function(e) {
                e.preventDefault();
                const zoomUrl = this.getAttribute('data-zoom-url');
                
                if (zoomUrl && zoomUrl !== '#' && zoomUrl !== 'https://zoom.us/j/placeholder') {
                    window.open(zoomUrl, '_blank');
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Reunión no disponible',
                        text: 'La reunión aún no está programada. Por favor, inténtalo más tarde.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
            
            // Manejar clic en botón "Compartir"
            document.getElementById('shareBtn').addEventListener('click', function() {
                const currentUrl = window.location.href;
                const referido = '<?php echo isset($_SESSION["referido"]) ? $_SESSION["referido"] : ""; ?>';
                
                let shareUrl = 'https://mizton.cat/meeting.php';
                if (referido) {
                    shareUrl += '?ref=' + referido;
                }
                
                // Copiar al portapapeles
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(shareUrl).then(function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Enlace Copiado!',
                            html: `El enlace de la presentación ha sido copiado al portapapeles.<br><br><strong>Compártelo con quien quieras invitar a conocer esta oportunidad.</strong>`,
                            confirmButtonText: 'Perfecto',
                            confirmButtonColor: '#28a745',
                            timer: 5000,
                            timerProgressBar: true
                        });
                    }).catch(function() {
                        fallbackCopy(shareUrl);
                    });
                } else {
                    fallbackCopy(shareUrl);
                }
            });
            
            // Función de fallback para copiar
            function fallbackCopy(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enlace Copiado!',
                        html: `El enlace de la presentación ha sido copiado al portapapeles.<br><br><strong>Compártelo con quien quieras invitar a conocer esta oportunidad.</strong>`,
                        confirmButtonText: 'Perfecto',
                        confirmButtonColor: '#28a745',
                        timer: 5000,
                        timerProgressBar: true
                    });
                } catch (err) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Enlace para Compartir',
                        html: `<p>Copia este enlace para compartir:</p><br><input type="text" value="${text}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" readonly onclick="this.select()">`,
                        confirmButtonText: 'Cerrar',
                        confirmButtonColor: '#667eea'
                    });
                }
                
                document.body.removeChild(textArea);
            }
        });
    </script>
</body>
</html>
