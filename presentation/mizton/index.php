<?php
// Capturar código de referido de la URL
$refCode = $_GET['ref'] ?? '';
$registerUrl = 'https://panel.mizton.cat/register.php';
if (!empty($refCode) && preg_match('/^[a-zA-Z0-9]+$/', $refCode)) {
    $registerUrl .= '?ref=' . urlencode($refCode);
    // Guardar en sesión para usar en la API de preguntas
    session_start();
    $_SESSION['sponsor_ref_code'] = $refCode;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mizton - Ecosistema de Tokenización RWA</title>
    <link rel="stylesheet" href="../css/presentation.css?v=19">
    <link rel="stylesheet" href="styles-extra.css?v=19">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="presentation-container">
        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progress-fill"></div>
        </div>

        <div class="slides-wrapper">
            <!-- Slide 0: Portada -->
            <div class="slide active cover-slide" data-slide="0">
                <div class="slide-content">
                    <div class="cover-logo fade-in delay-1">
                        <img src="img/logoMizton.png" alt="Mizton Logo" class="cover-logo-img">
                    </div>
                    <h1 class="cover-title fade-in delay-2">Comunidad<span class="mobile-break"></span> Mizton</h1>
                </div>
            </div>
            
            <!-- Slide 1: Intro -->
            <div class="slide" data-slide="1">
                <audio id="audio-slide-1">
                    <source src="audios/slide.01.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <div class="logo-container fade-in delay-1">
                        <span class="logo-icon">🏛️</span>
                    </div>
                    <h1 class="slide-title">Mizton</h1>
                    <p class="slide-subtitle">Ecosistema de Tokenización de Activos del Mundo Real</p>
                    <div class="intro-tagline fade-in delay-2">
                        <p>Desde <strong>2025</strong> inició el cambio definitivo del sistema económico mundial</p>
                        <p class="highlight-text">Descubre lo que el 99% de la población desconoce</p>
                    </div>
                </div>
            </div>

            <!-- Slide 2: Bienvenida -->
            <div class="slide" data-slide="2">
                <audio id="audio-slide-2">
                    <source src="audios/slide.02.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Bienvenidos</h2>
                    <div class="info-box fade-in delay-1">
                        <p>Una propuesta que puede cambiar <strong>tu economía</strong> y la de <strong>tus siguientes generaciones</strong></p>
                        <p>Esto es algo que ya está sucediendo y poca gente está prestando atención</p>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Disclaimer Completo -->
            <div class="slide" data-slide="3">
                <audio id="audio-slide-3" data-auto-progress="true">
                    <source src="audios/slide.03.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">LEER ANTES DE CONTINUAR</h2>
                    <div class="disclaimer-full">
                        <div class="disclaimer-step hidden" data-step="1">
                            <p>"MZT es un token corporativo de participación en el ecosistema Mizton de tokenización de activos del mundo real, RWA. No representa acciones, equity, participación societaria, derechos de cobro automático, dividendos ni rendimientos garantizados de ningún tipo."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="2">
                            <p>"Nada en este documento o presentación debe interpretarse como oferta pública de inversión, asesoría financiera, legal o fiscal, ni como promesa de retornos económicos. Los modelos de participación comunitaria descritos son aspiracionales y sujetos a viabilidad operativa, técnica y regulatoria."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="3">
                            <p>"Participar en este ecosistema implica riesgo alto, incluida la pérdida total de los recursos destinados."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="4">
                            <p>"El modelo de participación en resultados depende de condiciones operativas, legales y regulatorias, y es posible que los términos evolucionen según el marco regulatorio vigente."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="5">
                            <p>"Todas las cifras y proyecciones en este documento o presentación son estimaciones referenciales, NO están garantizadas, y el desempeño real del ecosistema puede ser significativamente menor o diferente."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="6">
                            <p>"La tenencia de Mizton no genera derechos automáticos sobre ingresos, beneficios económicos ni rendimientos de ningún tipo. El token representa participación en el ecosistema, no propiedad ni derechos financieros automáticos."</p>
                        </div>
                        <div class="disclaimer-step hidden" data-step="7">
                            <p>"Mizton no es un instrumento financiero, no genera derechos automáticos sobre ingresos ni rendimientos, y participar implica riesgo alto, incluida la pérdida total de los recursos destinados. Consulta asesores legales y fiscales independientes antes de participar."</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 4: Narrativa RWA -->
            <div class="slide" data-slide="4" data-auto-scroll-duration="8000">
                <audio id="audio-slide-4" data-auto-play="true" data-delay="1000">
                    <source src="audios/slide.04.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">La Narrativa RWA</h2>
                    <p class="slide-subtitle">Tokenización de Activos del Mundo Real</p>
                    
                    <div class="rwa-visual fade-in delay-1">
                        <div class="rwa-center">
                            <span class="rwa-icon">🌍</span>
                            <span class="rwa-label">Activos Reales</span>
                        </div>
                        <div class="rwa-arrows">
                            <span class="arrow">↓</span>
                            <span class="arrow">↓</span>
                            <span class="arrow">↓</span>
                        </div>
                        <div class="rwa-tokens">
                            <div class="token-item">
                                <span class="token-icon">🏢</span>
                                <span>Inmobiliario</span>
                            </div>
                            <div class="token-item">
                                <span class="token-icon">⚡</span>
                                <span>Energético</span>
                            </div>
                            <div class="token-item">
                                <span class="token-icon">🌾</span>
                                <span>Agroindustrial</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="explanation-box fade-in delay-2">
                        <p>La <strong>reestructuración más significativa</strong> de la infraestructura financiera global desde la digitalización bancaria</p>
                    </div>
                </div>
            </div>

            <!-- Slide 5: Proyección BCG -->
            <div class="slide" data-slide="5" data-auto-scroll-duration="8000">
                <audio id="audio-slide-5" data-auto-play="true" data-delay="1000">
                    <source src="audios/slide.05.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">El Mercado RWA</h2>
                    <p class="slide-subtitle">Proyección Boston Consulting Group</p>
                    
                    <div class="projection-visual fade-in delay-1">
                        <div class="projection-year">
                            <span class="year">2030</span>
                            <div class="projection-bar">
                                <div class="bar-fill"></div>
                            </div>
                            <span class="amount">$16 Trillones USD</span>
                        </div>
                    </div>
                    
                    <div class="actors-grid fade-in delay-2">
                        <div class="actor-card">
                            <span class="actor-icon">🏦</span>
                            <span>Bancos</span>
                        </div>
                        <div class="actor-card">
                            <span class="actor-icon">🏛️</span>
                            <span>Gobiernos</span>
                        </div>
                        <div class="actor-card">
                            <span class="actor-icon">🚀</span>
                            <span>Big Tech</span>
                        </div>
                        <div class="actor-card">
                            <span class="actor-icon">💼</span>
                            <span>Corporativos</span>
                        </div>
                    </div>
                    
                    <div class="info-box fade-in delay-3" style="background: rgba(0, 229, 255, 0.1); border-color: #00e5ff;">
                        <p><strong>No es el futuro:</strong> es un proceso <span style="color: #00e5ff; font-weight: 700;">ya en marcha</span></p>
                    </div>
                </div>
            </div>

            <!-- Slide 6: Video 1 - Grandes Actores -->
            <div class="slide" data-slide="6">
                <audio id="audio-slide-6" data-auto-play-after-video="true">
                    <source src="audios/slide.06.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content video-slide-content">
                    <h2 class="slide-title">Repasemos brevemente lo que ha acontecido a la fecha...</h2>
                    <div class="video-container">
                        <video id="video-slide-6" 
                               class="presentation-video"
                               playsinline
                               preload="auto">
                            <source src="videos/slide.06.mp4" type="video/mp4">
                            Tu navegador no soporta el elemento de video.
                        </video>
                        <!-- Overlay de mute/unmute -->
                        <div id="video-mute-overlay" class="video-mute-overlay" style="display: none;">
                            <span class="mute-icon">🔇</span>
                            <span class="mute-text">Click para activar sonido</span>
                        </div>
                    </div>
                    <p class="video-caption fade-in delay-1">
                        Podemos ver como los actores más grandes del planeta están prestando especial atención a esta nueva narrativa.<br>
                        <strong>¿Y tú, qué estás haciendo al respecto?</strong>
                    </p>
                </div>
            </div>

            <!-- Slide 7: Video 2 - Personas atención a narrativa -->
            <div class="slide" data-slide="7">
                <!-- Audio intro -->
                <audio id="audio-slide-7-1" data-sequence="intro">
                    <source src="audios/slide.07.1.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <!-- Audio outro -->
                <audio id="audio-slide-7-2" data-sequence="outro">
                    <source src="audios/slide.07.2.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content video-slide-content">
                    <h2 class="slide-title">Ahora conozcamos a personas que sí están prestando atención...</h2>
                    <div class="video-container" id="video-7-container">
                        <video id="video-slide-7" 
                               class="presentation-video news-video"
                               playsinline
                               preload="auto"
                               style="display: none;">
                            <source src="" type="video/mp4">
                            Tu navegador no soporta el elemento de video.
                        </video>
                        <!-- Overlay de carga inicial -->
                        <div id="video-7-loading" class="video-loading-overlay">
                            <span class="loading-text">Preparando contenido...</span>
                        </div>
                    </div>
                    <button id="load-random-video-btn" class="random-video-btn" style="display: none;">🎲 Cargar otro video</button>
                    <p class="video-caption fade-in delay-1">
                        Estas son las voces que están hablando sobre el futuro de las inversiones tokenizadas.
                    </p>
                </div>
            </div>

            <!-- Slide 8: Visión Mizton -->
            <div class="slide" data-slide="8">
                <audio id="audio-slide-8" data-auto-play="true">
                    <source src="audios/slide.08.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Visión del Ecosistema</h2>
                    <p class="slide-subtitle">Mizton aspira a construir un ecosistema donde las fuentes de valor incluyan:</p>
                    
                    <p class="click-hint fade-in">👆 Haz clic en cada card para revelar más información</p>
                    
                    <div class="vision-grid fade-in delay-1">
                        <div class="vision-card clickable blur-reveal" data-reveal-time="5266">
                            <div class="vision-splash"></div>
                            <div class="vision-icon">🏗️</div>
                            <h3>Tokenización</h3>
                            <p>Activos inmobiliarios, energéticos, agroindustriales</p>
                        </div>
                        <div class="vision-card clickable blur-reveal" data-reveal-time="10000">
                            <div class="vision-splash"></div>
                            <div class="vision-icon">👥</div>
                            <h3>Participación</h3>
                            <p>Comunitaria proporcional al portafolio de proyectos</p>
                        </div>
                        <div class="vision-card clickable blur-reveal" data-reveal-time="13866">
                            <div class="vision-splash"></div>
                            <div class="vision-icon">💰</div>
                            <h3>Revenue Sharing</h3>
                            <p>Reparto de resultados vinculado al desempeño</p>
                        </div>
                        <div class="vision-card clickable blur-reveal" data-reveal-time="19133">
                            <div class="vision-splash"></div>
                            <div class="vision-icon">🛒</div>
                            <h3>Marketplace</h3>
                            <p>Proyectos tokenizados para diversificación directa</p>
                        </div>
                        <div class="vision-card clickable blur-reveal" data-reveal-time="23133" style="grid-column: span 2; max-width: 400px; margin: 0 auto;">
                            <div class="vision-splash"></div>
                            <div class="vision-icon">🌟</div>
                            <h3>Transición de Riqueza Generacional</h3>
                            <p>La participación en la Comunidad Mizton representa la transición más grande de riqueza generacional de la historia</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 9: Modelo de Participación -->
            <div class="slide" data-slide="9" data-auto-scroll-duration="8000">
                <audio id="audio-slide-9" data-auto-play="true">
                    <source src="audios/slide.09.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Modelo de Participación</h2>
                    
                    <div class="model-diagram fade-in delay-1">
                        <div class="model-center">
                            <span class="model-icon">🏛️</span>
                            <span class="model-label">Mizton</span>
                        </div>
                        <div class="model-ring">
                            <div class="model-point" style="--i:0">
                                <span>🤝</span>
                                <small>Comunidad</small>
                            </div>
                            <div class="model-point" style="--i:1">
                                <span>🔗</span>
                                <small>Blockchain</small>
                            </div>
                            <div class="model-point" style="--i:2">
                                <span>🏢</span>
                                <small>Proyectos RWA</small>
                            </div>
                            <div class="model-point" style="--i:3">
                                <span>💵</span>
                                <small>Ganancias Reales</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box fade-in delay-2">
                        <p>Mizton es una compañía de <strong>Tokenización de Activos del Mundo Real</strong> participando de la narrativa mundial a través de proyectos propios y de terceros</p>
                        <p style="margin-top: 10px; color: #00e5ff;">Ganancias reales, transparentes, verificables y seguras</p>
                    </div>
                </div>
            </div>

            <!-- Slide 10: Qué Obtienes -->
            <div class="slide" data-slide="10">
                <audio id="audio-slide-10" data-auto-play="true">
                    <source src="audios/slide.10.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">¿Qué Obtienes?</h2>
                    <p class="slide-subtitle">Participación accesible desde $20 USD</p>
                    
                    <div class="package-visual fade-in delay-1">
                        <div class="package-box">
                            <div class="package-price">$20</div>
                            <div class="package-tokens">= 200 MZT</div>
                            <div class="package-badge normal">NORMAL</div>
                        </div>
                        <div class="package-arrow">➜</div>
                        <div class="package-box highlight clickable blur-reveal" data-reveal-time="10800">
                            <div class="vision-splash"></div>
                            <div class="package-price">$20</div>
                            <div class="package-tokens" style="color: #00e5ff;">= 400 MZT</div>
                            <div class="package-badge fast-active">FAST ACTIVE</div>
                        </div>
                    </div>
                    
                    <div class="fast-active-info fade-in delay-2">
                        <h4>🚀 Bono FAST ACTIVE (30 días)</h4>
                        <ul>
                            <li class="clickable" data-reveal-time="21566">• Primer paquete: <strong>400 MZT</strong> (30 días FAST ACTIVE)</li>
                            <li class="clickable" data-reveal-time="25833">• Segundo paquete (día 20): <strong>400 MZT</strong> (doble)</li>
                            <li class="clickable" data-reveal-time="31166">• Paquetes después del día 30: <strong>200 MZT</strong></li>
                            <li class="clickable" data-reveal-time="38200">• Todos los paquetes durante FAST ACTIVE: <strong>DOBLE tokens</strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Slide 11: Pool Global -->
            <div class="slide" data-slide="11">
                <audio id="audio-slide-11" data-auto-play="true">
                    <source src="audios/slide.11.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Pool Global de Ganancias</h2>
                    
                    <div class="pool-visual fade-in delay-1">
                        <div class="pool-container">
                            <div class="pool-liquid"></div>
                            <div class="pool-label">POOL GLOBAL</div>
                            <div class="pool-sublabel">Mizton</div>
                        </div>
                        <div class="pool-tokens">
                            <div class="pool-token-flow">
                                <span>🏢</span>
                                <small>Proyectos RWA</small>
                                <span class="flow-arrow">↓</span>
                            </div>
                            <div class="pool-token-flow">
                                <span>💰</span>
                                <small>Ingresos</small>
                                <span class="flow-arrow">↓</span>
                            </div>
                            <div class="pool-token-flow">
                                <span>🪙</span>
                                <small>Reparto</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="warning-text fade-in delay-3" data-reveal-time="15066">
                        ⚠️ Máxima crypto: <strong>NUNCA</strong> inviertas lo que no estés dispuesto a perder
                    </div>

                    <div class="pool-strategy fade-in delay-2 clickable blur-reveal" data-reveal-time="22900">
                        <div class="vision-splash"></div>
                        <h4>📈 Estrategia Recomendada</h4>
                        <p>Adquirir <strong>al menos 1 paquete cada 30 días</strong></p>
                        <p>Cada mes sumas más tokens MZT → Mayor participación en el Pool Global</p>
                    </div>
                </div>
            </div>

            <!-- Slide 12: Conformación Pool -->
            <div class="slide" data-slide="12">
                <audio id="audio-slide-12" data-auto-play="true">
                    <source src="audios/slide.12.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">¿Cómo se Conforma?</h2>
                    
                    <div class="pool-formation fade-in delay-1">
                        <div class="formation-step clickable blur-reveal" data-reveal-time="3000">
                            <div class="vision-splash"></div>
                            <div class="step-icon">🏢</div>
                            <div class="step-content">
                                <h4>Proyectos Tokenizados</h4>
                                <p>Diversos proyectos RWA generan comisiones y pagos de servicios</p>
                            </div>
                        </div>
                        <div class="formation-arrow">↓</div>
                        <div class="formation-step highlight clickable blur-reveal" data-reveal-time="13800">
                            <div class="vision-splash"></div>
                            <div class="step-icon">💰</div>
                            <div class="step-content">
                                <h4>Pool Global</h4>
                                <p>Ingresos consolidados de todos los proyectos activos</p>
                            </div>
                        </div>
                        <div class="formation-arrow">↓</div>
                        <div class="formation-step clickable blur-reveal" data-reveal-time="17500">
                            <div class="vision-splash"></div>
                            <div class="step-icon">🔄</div>
                            <div class="step-content">
                                <h4>Reparto Perpetuo</h4>
                                <p>Dinámico, transparente, auditable e inmutable mes con mes</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="explanation-box clickable blur-reveal" data-reveal-time="26200">
                        <div class="vision-splash"></div>
                        <p><strong>Ticket de participación:</strong> MZT no es un token especulativo, sino acceso a múltiples proyectos consolidados en un pool único</p>
                        <p style="margin-top: 10px; color: #00e5ff;">El éxito es sistémico — no depende de un solo activo</p>
                    </div>
                </div>
            </div>

            <!-- Slide 13: Resumen Modelo -->
            <div class="slide" data-slide="13">
                <audio id="audio-slide-13" data-auto-play="true">
                    <source src="audios/slide.13.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Resumen del Modelo</h2>
                    <p class="slide-subtitle">Haz clic en cada número para revelar los pasos</p>
                    
                    <div class="summary-timeline fade-in delay-1">
                        <div class="timeline-item clickable" data-reveal-time="4000">
                            <div class="timeline-number">1</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">👋</span>
                                <p>Formas parte de la <strong>Comunidad Mizton</strong></p>
                            </div>
                        </div>
                        <div class="timeline-item clickable" data-reveal-time="8000">
                            <div class="timeline-number">2</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">🪙</span>
                                <p>Recibes <strong>Tokens MZT</strong> con Vesting 360 días</p>
                            </div>
                        </div>
                        <div class="timeline-item clickable" data-reveal-time="13000">
                            <div class="timeline-number">3</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">⏱️</span>
                                <p>Liberación <strong>gradual cada segundo</strong></p>
                            </div>
                        </div>
                        <div class="timeline-item clickable" data-reveal-time="16000">
                            <div class="timeline-number">4</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">💰</span>
                                <p>Participación en <strong>Pool Global</strong> sobre tokens liberados</p>
                            </div>
                        </div>
                        <div class="timeline-item clickable" data-reveal-time="21333">
                            <div class="timeline-number">5</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">➕</span>
                                <p>Agrega más tokens <strong>cuando quieras</strong>. Recomendación: 1 c/30 días</p>
                            </div>
                        </div>
                        <div class="timeline-item clickable" data-reveal-time="29233">
                            <div class="timeline-number">6</div>
                            <div class="timeline-content">
                                <span class="timeline-icon">📈</span>
                                <p><strong>Diversificación sistémica</strong> con nuevos proyectos mensuales</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 14: Proyectos Tokenizados -->
            <div class="slide" data-slide="14">
                <audio id="audio-slide-14" data-auto-play="true">
                    <source src="audios/slide.14.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Proyectos Tokenizados</h2>
                    
                    <div class="project-info-box fade-in">
                        <p>💡 <strong>Recuerda:</strong> Siempre que tengas en tu posesión <strong>1 token Mizton</strong>, participarás automáticamente del <strong>Pool Global</strong> de forma perpetua — y no solo de un proyecto, sino de <strong>todos los proyectos activos presentes y futuros</strong>.</p>
                    </div>
                    
                    <p class="click-hint fade-in delay-1">👆 Haz clic en cada proyecto para revelar sus detalles</p>
                    
                    <div class="projects-grid fade-in delay-1">
                        <div class="project-card kimen clickable blur-reveal" data-reveal-time="16966">
                            <div class="vision-splash"></div>
                            <div class="project-header">
                                <span class="project-icon">📚</span>
                                <h3>KIMEN</h3>
                            </div>
                            <p class="project-desc">Obra literaria <em>"Un Bello Mundo por Extrañar"</em></p>
                            <ul class="project-features">
                                <li>Publicación Amazon Books</li>
                                <li>Saga expandible</li>
                                <li>Filosofía + narrativa especulativa</li>
                                <li>Comunidad cultural Web3</li>
                            </ul>
                            <div class="project-arrow-container">
                                <span class="project-arrow" data-target="token-kimen">⬇</span>
                                <img id="token-kimen" src="img/kimen-token.png" alt="Token KIMEN" class="project-token-image" data-reveal-time="31000">
                            </div>
                        </div>
                        
                        <div class="project-card dxip clickable blur-reveal" data-reveal-time="51166">
                            <div class="vision-splash"></div>
                            <div class="project-header">
                                <span class="project-icon">📦</span>
                                <h3>DXIP</h3>
                            </div>
                            <p class="project-desc">Dropxip — Infraestructura dropshipping marca blanca</p>
                            <ul class="project-features">
                                <li>Sin inventario ni logística</li>
                                <li>Vendedores afiliados</li>
                                <li>5 años de vigencia</li>
                                <li>Participación en resultados</li>
                            </ul>
                            <div class="project-arrow-container">
                                <span class="project-arrow" data-target="token-dxip">⬇</span>
                                <img id="token-dxip" src="img/dxip-token.png" alt="Token DXIP" class="project-token-image" data-reveal-time="72533">
                            </div>
                        </div>
                        
                        <div class="project-card dmx clickable blur-reveal" data-reveal-time="82200">
                            <div class="vision-splash"></div>
                            <div class="project-header">
                                <span class="project-icon">🏠</span>
                                <h3>DMX</h3>
                            </div>
                            <p class="project-desc">Dommexia — Transformando el inmobiliario LATAM</p>
                            <ul class="project-features">
                                <li>Buscador de propiedades</li>
                                <li>Conexión directa</li>
                                <li>5 años de desarrollo</li>
                                <li>Claro, directo, eficiente</li>
                            </ul>
                            <div class="project-arrow-container">
                                <span class="project-arrow" data-target="token-dmx">⬇</span>
                                <img id="token-dmx" src="img/dmx-token.png" alt="Token DMX" class="project-token-image" data-reveal-time="109066">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 15: Participación Global vs Directa -->
            <div class="slide" data-slide="15">
                <audio id="audio-slide-15" data-auto-play="true">
                    <source src="audios/slide.15.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Participación Dual</h2>
                    
                    <div class="dual-participation fade-in delay-1">
                        <div class="participation-card global">
                            <div class="participation-header">
                                <span>🌍</span>
                                <h3>Global</h3>
                            </div>
                            <p>Con solo <strong>1 MZT</strong> participas del Pool Global de forma <span class="highlight-perpetual">perpetua</span></p>
                            <p class="participation-scope">Todos los proyectos activos, presentes y futuros</p>
                        </div>
                        
                        <div class="participation-plus">+</div>
                        
                        <div class="participation-card direct clickable blur-reveal" data-reveal-time="2333">
                            <div class="vision-splash"></div>
                            <div class="participation-header">
                                <span>🎯</span>
                                <h3>Directa</h3>
                            </div>
                            <p>Adquiere tokens de proyectos específicos que te agraden</p>
                            <p class="participation-scope">KIMEN, DXIP, DMX, y futuros proyectos</p>
                        </div>
                    </div>
                    
                    <div class="explanation-box clickable blur-reveal" data-reveal-time="6600">
                            <div class="vision-splash"></div>
                        <p><strong>Ganas de dos formas:</strong> Global (Pool Mizton) + Directa (Proyectos individuales)</p>
                    </div>
                </div>
            </div>

            <!-- Slide 16: El Proceso -->
            <div class="slide" data-slide="16">
                <audio id="audio-slide-16" data-auto-play="true">
                    <source src="audios/slide.16.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">El Proceso de Tokenización</h2>
                    <p class="slide-subtitle">Desde elegibilidad hasta primer bono</p>
                    
                    <div class="process-timeline fade-in delay-1">
                        <div class="process-phase clickable blur-reveal" data-reveal-time="10000">
                            <div class="vision-splash"></div>
                            <div class="phase-icon">🔍</div>
                            <div class="phase-content">
                                <h4>Estudio</h4>
                                <p>Evaluación de candidatos y factibilidad</p>
                            </div>
                        </div>
                        <div class="process-arrow">→</div>
                        <div class="process-phase clickable blur-reveal" data-reveal-time="18433">
                            <div class="vision-splash"></div>                           <div class="phase-icon">💻</div>
                            <div class="phase-content">
                                <h4>Desarrollo</h4>
                                <p>Contrato inteligente y tecnología</p>
                            </div>
                        </div>
                        <div class="process-arrow">→</div>
                        <div class="process-phase clickable blur-reveal" data-reveal-time="21466">
                            <div class="vision-splash"></div>                           <div class="phase-icon">📢</div>
                            <div class="phase-content">
                                <h4>Difusión</h4>
                                <p>Mercadotecnia y acompañamiento</p>
                            </div>
                        </div>
                        <div class="process-arrow">→</div>
                        <div class="process-phase highlight clickable blur-reveal" data-reveal-time="30333">
                            <div class="vision-splash"></div>
                            <div class="phase-icon">🎉</div>
                            <div class="phase-content">
                                <h4>Primer Bono</h4>
                                <p>Ejecución productiva del proyecto</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="time-indicator clickable blur-reveal" data-reveal-time="33600">
                        <div class="vision-splash"></div>
                        <span class="time-icon">⏱️</span>
                        <span class="time-text">3 a 9 meses</span>
                    </div>
                    
                    <div class="info-box clickable blur-reveal" data-reveal-time="40000">
                        <div class="vision-splash"></div>
                        <p>El <strong>Vesting de 360 días</strong> se ajusta al tiempo que toma un proyecto generar ganancias</p>
                        <p style="margin-top: 8px; color: #00e5ff;">Cuando tus tokens estén liberados, los proyectos ya estarán generando</p>
                    </div>
                </div>
            </div>

            <!-- Slide 17: Mientras Tanto -->
            <div class="slide" data-slide="17">
                <audio id="audio-slide-17" data-auto-play="true">
                    <source src="audios/slide.17.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">¿Y Mientras Tanto?</h2>
                    <p class="slide-subtitle">Capitalización paralela con ingresos inmediatos</p>
                    
                    <div class="meanwhile-visual fade-in delay-1">
                        <div class="meanwhile-box">
                            <div class="meanwhile-icon">⏳</div>
                            <h4>Tokenización</h4>
                            <p>3-9 meses</p>
                        </div>
                        <div class="meanwhile-parallel">
                            <span class="parallel-line">|</span>
                            <span class="parallel-text">PARALELO</span>
                            <span class="parallel-line">|</span>
                        </div>
                        <div class="meanwhile-box highlight clickable blur-reveal" data-reveal-time="6566">
                            <div class="vision-splash"></div>
                            <div class="meanwhile-icon">💵</div>
                            <h4>Ingresos Inmediatos</h4>
                            <p>Programa de Referidos</p>
                        </div>
                    </div>
                    
                    <div class="explanation-box fade-in delay-2">
                        <p>Mizton ofrece un <strong>modelo de capitalización paralela</strong> para obtener ingresos mientras transcurre el proceso de tokenización</p>
                    </div>
                </div>
            </div>

            <!-- Slide 18: Programa de Referidos -->
            <div class="slide" data-slide="18">
                <audio id="audio-slide-18" data-auto-play="true">
                    <source src="audios/slide.18.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Programa de Referidos</h2>
                    <p class="slide-subtitle">Plan de beneficios increíbles</p>
                    
                    <div class="referral-intro fade-in delay-1 clickable blur-reveal" data-reveal-time="4633">
                        <div class="vision-splash"></div>
                        <div class="vigencia-box">
                            <div class="vigencia-content">
                                <p class="vigencia-example"><strong>Qué otorga cada paquete?</strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="benefits-preview fade-in delay-2">
                        <div class="benefit-item clickable blur-reveal" data-reveal-time="8566">
                            <div class="vision-splash"></div>
                            <span class="benefit-number"></span>
                            <span class="benefit-text">30 días acumulativos</span>
                        </div>
                        <div class="benefit-item clickable blur-reveal" data-reveal-time="13800">
                            <div class="vision-splash"></div>
                            <span class="benefit-number"></span>
                            <span class="benefit-text">ej. 5 paquetes = 150 días de vigencia</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 19: Beneficio 1 - Reducción Vesting -->
            <div class="slide" data-slide="19">
                <audio id="audio-slide-19" data-auto-play="true">
                    <source src="audios/slide.19.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Beneficio 1: Reducción de Vesting</h2>
                    
                    <div class="vesting-reduction fade-in delay-1">
                        <div class="vesting-base">
                            <span class="vesting-days">360</span>
                            <span class="vesting-label">días base</span>
                        </div>
                        <div class="vesting-arrows">
                            <span>↓</span>
                            <span>↓</span>
                            <span>↓</span>
                        </div>
                        <div class="vesting-levels">
                            <div class="vesting-level clickable blur-reveal" data-reveal-time="10633">
                                <div class="vision-splash"></div>
                                <div class="level-referrals">1</div>
                                <div class="level-days">300 días</div>
                                <div class="level-savings">-60 días</div>
                            </div>
                            <div class="vesting-level clickable blur-reveal" data-reveal-time="14400">
                                <div class="vision-splash"></div>
                                <div class="level-referrals">2</div>
                                <div class="level-days">240 días</div>
                                <div class="level-savings">-120 días</div>
                            </div>
                            <div class="vesting-level highlight clickable blur-reveal" data-reveal-time="18433">
                                <div class="vision-splash"></div>
                                <div class="level-referrals">3+</div>
                                <div class="level-days" style="color: #00e5ff;">180 días</div>
                                <div class="level-savings" style="color: #00e5ff;">-180 días</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box fade-in delay-2 clickable blur-reveal" data-reveal-time="20000">
                        <div class="vision-splash"></div>
                        <p>Con <strong>3 referidos activos o más</strong>, tu Vesting se reduce a la mitad: de 360 a 180 días</p>
                        <p style="margin-top: 8px;">Aplica a <strong>todos tus tokens</strong> existentes y futuros</p>
                    </div>
                </div>
            </div>

            <!-- Slide 20: Beneficio 2 - Bonos 50% -->
            <div class="slide" data-slide="20">
                <audio id="audio-slide-20" data-auto-play="true">
                    <source src="audios/slide.20.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Beneficio 2: Bonos 50%</h2>
                    <p class="slide-subtitle">De tus referidos y sus referidos (2do nivel)</p>
                    
                    <div class="bonus-visual fade-in delay-1">
                        <div class="bonus-flow">
                            <div class="bonus-person you">
                                <span>👤</span>
                                <small>Tú</small>
                            </div>
                            <div class="bonus-connections">
                                <div class="connection-level clickable blur-reveal" data-reveal-time="2466">
                                    <div class="vision-splash"></div>
                                    <span class="connection-arrow">↓</span>
                                    <span class="connection-label">Directos</span>
                                    <span class="connection-percent">50%</span>
                                </div>
                                <div class="connection-level clickable blur-reveal" data-reveal-time="4366">
                                    <div class="vision-splash"></div>
                                    <span class="connection-arrow">↓</span>
                                    <span class="connection-label">2do Nivel</span>
                                    <span class="connection-percent">50%</span>
                                </div>
                            </div>
                            <div class="bonus-results clickable blur-reveal" data-reveal-time="9566">
                                <div class="vision-splash"></div>
                                <div class="bonus-result">
                                    <span>🪙</span>
                                    <small>Tokens durante TODO su primer año</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="explanation-box clickable blur-reveal" data-reveal-time="11633">
                        <div class="vision-splash"></div>
                        <p>Recibes <strong>50% de los tokens</strong> que ellos reciban — no solo del primer mes, sino durante <strong>TODO su primer año</strong></p>
                        <p style="margin-top: 8px; color: #00e5ff;">Exponencia la cantidad de MZT en tu posesión</p>
                    </div>
                </div>
            </div>

            <!-- Slide 21: Beneficio 3 - Red Personal -->
            <div class="slide" data-slide="21">
                <audio id="audio-slide-21" data-auto-play="true">
                    <source src="audios/slide.21.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Beneficio 3: Red Personal</h2>
                    <p class="slide-subtitle">Matriz 2×2 hasta 10 niveles</p>
                    
                    <div class="personal-net-visual fade-in delay-1">
                        <div class="net-structure">
                            <div class="net-level level-0">
                                <div class="net-node you">Tú</div>
                            </div>
                            <div class="net-branches">
                                <div class="net-branch">
                                    <div class="net-node">R1</div>
                                    <div class="net-sub">
                                        <div class="net-node">R3</div>
                                        <div class="net-node">R4</div>
                                    </div>
                                </div>
                                <div class="net-branch">
                                    <div class="net-node">R2</div>
                                    <div class="net-sub">
                                        <div class="net-node">R5</div>
                                        <div class="net-node">R6</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="net-stats">
                            <div class="stat-box clickable blur-reveal" data-reveal-time="15500">
                                <div class="vision-splash"></div>
                                <span class="stat-number">10</span>
                                <span class="stat-label">Niveles</span>
                            </div>
                            <div class="stat-box highlight clickable blur-reveal" data-reveal-time="17933">
                                <div class="vision-splash"></div>
                                <span class="stat-number">2,046</span>
                                <span class="stat-label">Personas</span>
                            </div>
                            <div class="stat-box clickable blur-reveal" data-reveal-time="24333">
                                <div class="vision-splash"></div>
                                <span class="stat-number">$1</span>
                                <span class="stat-label">Por paquete</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box clickable blur-reveal" data-reveal-time="26500">
                        <div class="vision-splash"></div>
                        <p><strong>$1 por paquete</strong> adquirido por cualquiera de estas 2,046 personas, en cualquier momento, de forma perpetua</p>
                    </div>
                </div>
            </div>

            <!-- Slide 22: Beneficio 4 - Red Global -->
            <div class="slide" data-slide="22">
                <audio id="audio-slide-22" data-auto-play="true">
                    <source src="audios/slide.22.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Beneficio 4: Red Global</h2>
                    <p class="slide-subtitle">Matriz automática de paquetes mundiales</p>
                    
                    <div class="your-pack-indicator fade-in delay-1">
                        <span class="pack-item your-pack" style="font-size: 1.5rem;">📦</span>
                        <span style="margin-left: 10px; color: #e040fb; font-weight: bold;">Ejemplo de tus paquetes colocados en la Red Global</span>
                    </div>
                    
                    <div class="global-net-visual fade-in delay-2">
                        <div class="global-structure">
                            <div class="global-levels">
                                <!-- Nivel 1: 1 paquete -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="4700">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 2: 2 paquetes -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="5700">📦</span>
                                        <span class="pack-item" data-delay="6700">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 3: 4 paquetes (3er paquete violeta = usuario) -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="7700">📦</span>
                                        <span class="pack-item" data-delay="8700">📦</span>
                                        <span class="pack-item your-pack" data-delay="9700">📦</span>
                                        <span class="pack-item" data-delay="10700">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 4: 7 paquetes -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="11700">📦</span>
                                        <span class="pack-item" data-delay="12700">📦</span>
                                        <span class="pack-item" data-delay="13700">📦</span>
                                        <span class="pack-item" data-delay="14700">📦</span>
                                        <span class="pack-item" data-delay="15700">📦</span>
                                        <span class="pack-item" data-delay="16700">📦</span>
                                        <span class="pack-item" data-delay="17700">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 5: 10 paquetes (paquetes 4-10 violetas) -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="18700">📦</span>
                                        <span class="pack-item" data-delay="19700">📦</span>
                                        <span class="pack-item" data-delay="20700">📦</span>
                                        <span class="pack-item your-pack" data-delay="28166">📦</span>
                                        <span class="pack-item your-pack" data-delay="28366">📦</span>
                                        <span class="pack-item your-pack" data-delay="28566">📦</span>
                                        <span class="pack-item your-pack" data-delay="28766">📦</span>
                                        <span class="pack-item your-pack" data-delay="28966">📦</span>
                                        <span class="pack-item your-pack" data-delay="29166">📦</span>
                                        <span class="pack-item your-pack" data-delay="29366">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 6: 13 paquetes (paquetes 1-5 violetas) -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item your-pack" data-delay="29566">📦</span>
                                        <span class="pack-item your-pack" data-delay="29766">📦</span>
                                        <span class="pack-item your-pack" data-delay="29966">📦</span>
                                        <span class="pack-item your-pack" data-delay="30166">📦</span>
                                        <span class="pack-item your-pack" data-delay="30366">📦</span>
                                        <span class="pack-item" data-delay="37900">📦</span>
                                        <span class="pack-item" data-delay="38100">📦</span>
                                        <span class="pack-item" data-delay="38300">📦</span>
                                        <span class="pack-item" data-delay="38500">📦</span>
                                        <span class="pack-item" data-delay="38700">📦</span>
                                        <span class="pack-item" data-delay="38900">📦</span>
                                        <span class="pack-item" data-delay="39100">📦</span>
                                        <span class="pack-item" data-delay="39300">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 7: 16 paquetes -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="39500">📦</span>
                                        <span class="pack-item" data-delay="39700">📦</span>
                                        <span class="pack-item" data-delay="39900">📦</span>
                                        <span class="pack-item" data-delay="40100">📦</span>
                                        <span class="pack-item" data-delay="40300">📦</span>
                                        <span class="pack-item" data-delay="40500">📦</span>
                                        <span class="pack-item" data-delay="40700">📦</span>
                                        <span class="pack-item" data-delay="40900">📦</span>
                                        <span class="pack-item" data-delay="41100">📦</span>
                                        <span class="pack-item" data-delay="41300">📦</span>
                                        <span class="pack-item" data-delay="41500">📦</span>
                                        <span class="pack-item" data-delay="41700">📦</span>
                                        <span class="pack-item" data-delay="41900">📦</span>
                                        <span class="pack-item" data-delay="42100">📦</span>
                                        <span class="pack-item" data-delay="42300">📦</span>
                                        <span class="pack-item" data-delay="42500">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 8: 19 paquetes -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="42700">📦</span>
                                        <span class="pack-item" data-delay="42900">📦</span>
                                        <span class="pack-item" data-delay="43100">📦</span>
                                        <span class="pack-item" data-delay="43300">📦</span>
                                        <span class="pack-item" data-delay="43500">📦</span>
                                        <span class="pack-item" data-delay="43700">📦</span>
                                        <span class="pack-item" data-delay="43900">📦</span>
                                        <span class="pack-item" data-delay="44100">📦</span>
                                        <span class="pack-item" data-delay="44300">📦</span>
                                        <span class="pack-item" data-delay="44500">📦</span>
                                        <span class="pack-item" data-delay="44700">📦</span>
                                        <span class="pack-item" data-delay="44900">📦</span>
                                        <span class="pack-item" data-delay="45100">📦</span>
                                        <span class="pack-item" data-delay="45300">📦</span>
                                        <span class="pack-item" data-delay="45500">📦</span>
                                        <span class="pack-item" data-delay="45700">📦</span>
                                        <span class="pack-item" data-delay="45900">📦</span>
                                        <span class="pack-item" data-delay="46100">📦</span>
                                        <span class="pack-item" data-delay="46300">📦</span>
                                    </div>
                                </div>
                                
                                <!-- Nivel 9: puntos suspensivos -->
                                <div class="net-branch">
                                    <div class="net-packs">
                                        <span class="pack-item" data-delay="46500">...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="global-stats">
                            <div class="gstat">
                                <span class="gstat-icon">📦</span>
                                <span class="gstat-value">1M+</span>
                                <span class="gstat-label">Potencial de paquetes</span>
                            </div>
                            <div class="gstat highlight">
                                <span class="gstat-icon">💰</span>
                                <span class="gstat-value">$1M+</span>
                                <span class="gstat-label">Potencial por paquete</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="strategy-tip fade-in delay-2">
                        <span class="tip-icon">💡</span>
                        <p><strong>Entre más pronto</strong> coloques tus paquetes, mejor posición en la Red Global</p>
                    </div>
                </div>
            </div>

            <!-- Slide 23: Resumen Final -->
            <div class="slide" data-slide="23">
                <audio id="audio-slide-23" data-auto-play="true">
                    <source src="audios/slide.23.1.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <audio id="audio-slide-23-2">
                    <source src="audios/slide.23.2.mp3" type="audio/mpeg">
                    Tu navegador no soporta el elemento de audio.
                </audio>
                <div class="slide-content">
                    <h2 class="slide-title">Resumen: Dos Formas de Apalancarte</h2>
                    
                    <div class="final-summary fade-in delay-1">
                        <div class="summary-path clickable blur-reveal" data-reveal-time="6766">
                            <div class="vision-splash"></div>
                            <div class="path-header">
                                <span class="path-icon">🌍</span>
                                <h3>Participación Sistémica</h3>
                            </div>
                            <ul class="path-features">
                                <li>Token MZT</li>
                                <li>Pool Global</li>
                                <li>Todos los proyectos</li>
                                <li>Revenue Sharing</li>
                            </ul>
                        </div>
                        
                        <div class="path-divider">+</div>
                        
                        <div class="summary-path optional clickable blur-reveal" data-reveal-time="12966">
                            <div class="vision-splash"></div>
                            <div class="path-header">
                                <span class="path-icon">👥</span>
                                <h3>Programa de Referidos</h3>
                                <span class="optional-badge">Opcional</span>
                            </div>
                            <ul class="path-features">
                                <li>Reducción de Vesting</li>
                                <li>Bonos 50%</li>
                                <li>Red Personal 2×2</li>
                                <li>Red Global Automática</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="cta-box fade-in delay-2 clickable blur-reveal" data-reveal-time-after-audio="900" data-reveal-audio="audio-slide-23-2">
                        <div class="vision-splash"></div>
                        <p class="cta-text">Actívate inmediatamente</p>
                        <p class="cta-sub">Entre más pronto lo hagas, mejor lugar en la Red Global</p>
                        <a href="<?php echo htmlspecialchars($registerUrl); ?>" class="cta-button" target="_blank">Registrarme Ahora</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Footer de controles -->
    <div class="slide-dots-container">
        <!-- Izquierda: toggle auto-play de medios -->
        <div class="footer-section footer-left">
            <label class="switch-control" title="Auto-reproducir audio y video al entrar a cada diapositiva">
                <input type="checkbox" id="toggle-autoplay-media" checked>
                <span class="switch-slider"></span>
            </label>
            <span class="footer-label">AUTO</span>
            <div class="manual-audio-btns" id="manual-audio-btns"></div>
        </div>

        <!-- Centro: navegación + puntos -->
        <div class="footer-section footer-center">
            <button class="footer-icon-btn" id="question-btn" title="Hacer una pregunta">❓</button>
            <button class="footer-nav-btn" id="prev-btn" title="Diapositiva anterior">❮</button>
            <div class="slide-dots" id="slide-dots"></div>
            <span class="slide-number-indicator" id="slide-number-indicator">0</span>
            <button class="footer-nav-btn" id="next-btn" title="Diapositiva siguiente">❯</button>
        </div>

        <!-- Derecha: controles extra + auto-run -->
        <div class="footer-section footer-right">
            <button class="footer-icon-btn" id="restart-btn" title="Reiniciar presentación">⟲</button>
            <button class="footer-icon-btn" id="fullscreen-btn" title="Pantalla completa">⛶</button>
            <button class="footer-autorun-btn" id="autorun-btn" title="Reproducción automática de principio a fin">▶ Auto-Play</button>
        </div>
    </div>

    <!-- Modal de Preguntas -->
    <div class="question-modal-overlay" id="question-modal-overlay">
        <div class="question-modal">
            <div class="question-modal-header">
                <h3>💬 Tus Preguntas</h3>
                <button class="question-modal-close" id="question-modal-close">&times;</button>
            </div>
            
            <div class="question-modal-body">
                <!-- Lista de preguntas previas -->
                <div class="question-list" id="question-list">
                    <p class="question-empty" id="question-empty">No has formulado preguntas aún.</p>
                </div>
                
                <!-- Formulario nueva pregunta -->
                <div class="question-form">
                    <textarea id="question-input" class="question-textarea" placeholder="Escribe tu pregunta aquí..." maxlength="1000" rows="3"></textarea>
                    <button class="question-submit-btn" id="question-submit-btn">Enviar pregunta</button>
                </div>
                
                <!-- Campo Email -->
                <div class="question-whatsapp-section" id="question-whatsapp-section">
                    <div class="question-whatsapp-header">
                        <label class="question-wa-toggle">
                            <input type="checkbox" id="question-wa-toggle">
                            <span class="question-wa-text">Proporcionar mi email para recibir respuestas directamente</span>
                        </label>
                        <button class="question-wa-save-btn" id="question-wa-save-btn" title="Guardar email" style="display: none;">
                            💾
                        </button>
                    </div>
                    <div class="question-wa-field" id="question-wa-field" style="display: none;">
                        <div class="question-wa-input-group">
                            <input type="email" id="question-wa-input" class="question-wa-input" placeholder="tu@correo.com" disabled style="width: 100%;">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="question-modal-footer">
                <p class="question-footer-text">Las respuestas le serán entregadas a la persona que te invitó o de lo contrario proporciónanos tu correo electrónico y te enviaremos directamente las respuestas.</p>
            </div>
        </div>
    </div>

    <script src="../includes/presentation-common.js?v=19"></script>
    <script src="script.js?v=19"></script>
</body>
</html>
