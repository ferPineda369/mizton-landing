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

// Enlace permanente de Zoom para transmisiones en vivo
define('ZOOM_LIVE_URL', 'https://us06web.zoom.us/j/84641377935?pwd=QFdxvac6RKZOm2GCtOiocxtrpjkpic.1');

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
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 50%, var(--primary-green) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Variables CSS del landing */
        :root {
            --primary-green: #1B4332;
            --secondary-green: #2D5A3D;
            --accent-green: #40916C;
            --light-green: #52B788;
            --bright-green: #74C69D;
            --pale-green: #95D5B2;
            --dark-bg: #0A1A0F;
            --darker-bg: #051008;
            --white: #FFFFFF;
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
            margin-top: 2rem;
            line-height: 1.1;
        }
        
        .meeting-info .highlight {
            background: linear-gradient(45deg, var(--bright-green), var(--light-green));
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
            color: var(--bright-green);
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
            color: var(--bright-green);
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
            box-shadow: 0 20px 40px rgba(64, 145, 108, 0.3);
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
            background: linear-gradient(90deg, var(--primary-green), var(--accent-green), var(--bright-green));
        }
        
        .zoom-preview {
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--primary-green) 100%);
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
            animation: pulse 2s infinite;
        }
        
        .live-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            animation: livePulse 1.5s infinite;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
            z-index: 3;
        }
        
        @keyframes livePulse {
            0%, 100% { 
                opacity: 1; 
                transform: scale(1);
            }
            50% { 
                opacity: 0.7; 
                transform: scale(1.05);
            }
        }
        
        .replay-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
            z-index: 3;
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
        
        
        .meeting-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-zoom {
            background: linear-gradient(135deg, var(--light-green) 0%, var(--accent-green) 100%);
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
            box-shadow: 0 10px 30px rgba(82, 183, 136, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .btn-share {
            background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
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
            box-shadow: 0 10px 30px rgba(45, 90, 61, 0.4);
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
            /* Asegurar que no haya overflow horizontal */
            * {
                box-sizing: border-box;
            }
            
            html, body {
                overflow-x: hidden;
                width: 100%;
                max-width: 100vw;
            }
            
            .meeting-hero {
                padding: 2rem 1rem;
                width: 100%;
                max-width: 100vw;
                box-sizing: border-box;
            }
            
            .meeting-container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                box-sizing: border-box;
            }
            
            .meeting-content {
                grid-template-columns: 1fr;
                /* Reorganizar orden: descripción → video → fichas de detalles */
                display: flex;
                flex-direction: column;
            }
            
            /* La información aparece primero en móvil */
            .meeting-info {
                order: 1;
                display: flex;
                flex-direction: column;
            }
            
            /* Dentro de meeting-info: título y descripción primero */
            .meeting-info h1,
            .meeting-info .meeting-description,
            .meeting-info .tokenization-intro {
                order: 1;
            }
            
            /* Fichas de detalles van al final */
            .meeting-details {
                order: 3;
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
                margin-top: 2rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            /* El video aparece después de la descripción pero antes de las fichas */
            .meeting-card {
                order: 2;
                margin: 2rem 0;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            
            /* Asegurar que el zoom-preview no se salga */
            .zoom-preview {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                padding: 2rem 1rem;
            }
            
            /* Botones responsivos */
            .meeting-actions {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                gap: 0.5rem;
            }
            
            .btn-zoom,
            .btn-share {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
                min-width: 0;
                flex: 1;
            }
            
            .detail-item {
                padding: 0.8rem;
                font-size: 0.9rem;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                min-height: auto;
            }
            
            .detail-item i {
                font-size: 1.5rem;
            }
            
            .detail-item h4 {
                font-size: 0.9rem;
            }
            
            .detail-item p {
                font-size: 0.8rem;
            }
            
            /* Ajustar título en móvil */
            .meeting-info h1 {
                font-size: 2.5rem;
                margin-top: 3rem;
            }
            
            .meeting-description {
                font-size: 1rem;
            }
            
            /* Asegurar que el texto no se salga */
            .tokenization-intro,
            .meeting-description,
            .detail-item p {
                word-wrap: break-word;
                overflow-wrap: break-word;
                hyphens: auto;
            }
            
            /* Asegurar que los iconos y elementos no se salgan */
            .live-indicator,
            .replay-indicator {
                max-width: calc(100% - 2rem);
                word-wrap: break-word;
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
                        <?php if ($zoomVideo['is_live'] ?? 0): ?>
                            <!-- Indicador EN VIVO palpitante -->
                            <div class="live-indicator">
                                <i class="fas fa-circle"></i> EN VIVO
                            </div>
                        <?php else: ?>
                            <!-- Indicador de REPETICIÓN -->
                            <div class="replay-indicator">
                                <i class="fas fa-play-circle"></i> REPETICIÓN
                            </div>
                        <?php endif; ?>
                        
                        <div class="zoom-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="zoom-title"><?php echo htmlspecialchars($zoomVideo['title']); ?></h3>
                        <p class="zoom-subtitle">Presentación Exclusiva de Oportunidad de Negocio</p>
                    </div>
                    
                    <div class="meeting-actions">
                        <?php if ($zoomVideo['is_live'] ?? 0): ?>
                            <!-- Modo EN VIVO: Botón "Unirse Ahora" con enlace permanente -->
                            <a href="#" class="btn-zoom" id="joinZoomBtn" data-zoom-url="<?php echo ZOOM_LIVE_URL; ?>" data-is-live="1">
                                <i class="fas fa-play"></i>
                                Unirse Ahora
                            </a>
                            <button class="btn-share" id="shareBtn" data-share-url="<?php echo ZOOM_LIVE_URL; ?>">
                                <i class="fas fa-share-alt"></i>
                                Compartir
                            </button>
                        <?php else: ?>
                            <!-- Modo REPETICIÓN: Botón "Ver Repetición" con enlace de BD -->
                            <a href="#" class="btn-zoom" id="joinZoomBtn" data-zoom-url="<?php echo htmlspecialchars($zoomVideo['zoom_url']); ?>" data-is-live="0">
                                <i class="fas fa-play-circle"></i>
                                Ver Repetición
                            </a>
                            <button class="btn-share" id="shareBtn" data-share-url="<?php echo htmlspecialchars($zoomVideo['zoom_url']); ?>">
                                <i class="fas fa-share-alt"></i>
                                Compartir
                            </button>
                        <?php endif; ?>
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
                const shareUrl = this.getAttribute('data-share-url');
                const isLive = document.getElementById('joinZoomBtn').getAttribute('data-is-live') === '1';
                const referido = '<?php echo isset($_SESSION["referido"]) ? $_SESSION["referido"] : ""; ?>';
                
                let finalShareUrl;
                let shareMessage;
                
                if (isLive) {
                    // Modo EN VIVO: Compartir enlace permanente de Zoom
                    finalShareUrl = shareUrl;
                    shareMessage = `El enlace de la reunión EN VIVO ha sido copiado al portapapeles.<br><br><strong>¡Compártelo para que se unan a la transmisión en vivo!</strong>`;
                } else {
                    // Modo REPETICIÓN: Compartir enlace de meeting.php con referido
                    finalShareUrl = 'https://mizton.cat/meeting.php';
                    if (referido) {
                        finalShareUrl += '?ref=' + referido;
                    }
                    shareMessage = `El enlace de la presentación ha sido copiado al portapapeles.<br><br><strong>Compártelo con quien quieras invitar a conocer esta oportunidad.</strong>`;
                }
                
                // Copiar al portapapeles
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(finalShareUrl).then(function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Enlace Copiado!',
                            html: shareMessage,
                            confirmButtonText: 'Perfecto',
                            confirmButtonColor: isLive ? '#dc3545' : '#28a745',
                            timer: 5000,
                            timerProgressBar: true
                        });
                    }).catch(function() {
                        fallbackCopy(finalShareUrl, shareMessage, isLive);
                    });
                } else {
                    fallbackCopy(finalShareUrl, shareMessage, isLive);
                }
            });
            
            // Función de fallback para copiar
            function fallbackCopy(text, message, isLive) {
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
                        html: message || `El enlace ha sido copiado al portapapeles.<br><br><strong>Compártelo con quien quieras invitar.</strong>`,
                        confirmButtonText: 'Perfecto',
                        confirmButtonColor: isLive ? '#dc3545' : '#28a745',
                        timer: 5000,
                        timerProgressBar: true
                    });
                } catch (err) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Enlace para Compartir',
                        html: `<p>Copia este enlace para compartir:</p><br><input type="text" value="${text}" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" readonly onclick="this.select()">`,
                        confirmButtonText: 'Cerrar',
                        confirmButtonColor: isLive ? '#dc3545' : '#667eea'
                    });
                }
                
                document.body.removeChild(textArea);
            }
        });
    </script>
</body>
</html>
