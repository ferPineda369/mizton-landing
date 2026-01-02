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
        $logFile = __DIR__ . '/landing_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        if (is_writable(dirname($logFile))) {
            file_put_contents($logFile, "[$timestamp] LANDING DEBUG: Referido válido guardado: " . $_SESSION['referido'] . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] LANDING DEBUG: Session ID: " . session_id() . "\n", FILE_APPEND);
            file_put_contents($logFile, "[$timestamp] LANDING DEBUG: URL original: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
        }
    }
}

include 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mizton - La ÚNICA Membresía Financiera Colectiva</title>
    <meta name="description" content="Invierte $20 USD en un futuro financiero seguro, innovador y comunitario. Bonos únicos en la industria. Conócelos.">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/">
    <meta property="og:title" content="Mizton - La ÚNICA Membresía Financiera Colectiva">
    <meta property="og:description" content="Invierte $20 USD en un futuro financiero seguro, innovador y comunitario. Bonos únicos en la industria. Conócelos.">
    <meta property="og:image" content="https://mizton.cat/social-preview.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Mizton">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://mizton.cat/">
    <meta property="twitter:title" content="Mizton - La ÚNICA Membresía Financiera Colectiva">
    <meta property="twitter:description" content="Invierte $20 USD en un futuro financiero seguro, innovador y comunitario. Bonos únicos en la industria. Conócelos.">
    <meta property="twitter:image" content="https://mizton.cat/social-preview.jpg">
    
    <!-- WhatsApp -->
    <meta property="og:locale" content="es_ES">
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Meta Pixel Code with AdBlocker Bypass -->
    <script src="fb-proxy.js"></script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=684765634652448&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <img src="logo.gif" alt="Mizton" class="logo">
                <span class="brand-text">Mizton</span>
            </div>
            <div class="nav-links">
                <a href="marketplace/" target="_blank">Marketplace</a>
                <a href="#como-funciona">¿Cómo Funciona?</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#faq">FAQ</a>
                <a href="news/" target="_blank">News</a>
                <a href="meeting.php<?php echo isset($_SESSION['referido']) ? '?ref=' . $_SESSION['referido'] : ''; ?>" class="meeting-nav">
                    <i class="fas fa-video"></i> Presentación
                </a>
                <a href="#unirse" class="cta-nav">Únete Ahora</a>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-particles"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><br>
                    <span class="hero-title-main">Forma parte del Movimiento que Cambiará la Economía:</span>
                    <span class="highlight">Una Membresía que podrías recuperar?</span>
                </h1>
                <p class="hero-subtitle">
                Participa desde $20 USD en una propuesta innovadora y comunitaria. Construye junto a nosotros nuevas formas de gestionar tu economía familiar.
                </p>
                
                <!-- Información del referido (se muestra si hay código válido) -->
                <div id="referral-info" class="referral-info" style="display: none;">
                    <div class="referral-card">
                        <div class="referral-header">
                            <i class="fas fa-user-friends"></i>
                            <span>Invitado por</span>
                        </div>
                        <div class="referral-content">
                            <h4 id="referrer-name">Cargando...</h4>
                            <p id="referrer-type">Tipo de miembro</p>
                        </div>
                    </div>
                </div>
                <div class="hero-ctas">
                    <a href="#unirse" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Quiero Unirme Ahora
                    </a>
                    <a href="#whatsapp" class="btn btn-secondary">
                        <i class="fab fa-whatsapp"></i>
                        Quiero saber más
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Opcionalmente solicita tu aporte</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">976+</span>
                        <span class="stat-label">Comunidad creciente</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">$20</span>
                        <span class="stat-label">Participación accesible desde</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Propuesta de Valor -->
    <section class="value-proposition">
        <div class="container">
            <div class="section-header">
                <h2>Participación transparente, gestionada por contratos inteligentes públicos.</h2>
            </div>
            <div class="value-content">
                <div class="value-text">
                    <p class="value-main">
                    Participa hoy al menos durante <strong>360 días</strong> para probar la comunidad.
                    Mientras tanto, disfruta <strong>ganancias mensuales automáticas</strong> gracias a contratos inteligentes.
                    </p>
                    <p class="value-secondary">
                        En Mizton tú decides: <strong>esperar y ganar con la comunidad</strong> o 
                        acceder a <strong>recompra con liquidez real</strong>.
                    </p>
                </div>
                <div class="value-visual">
                    <div class="investment-flow">
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <span>Participa con $20+</span>
                        </div>
                        <div class="flow-arrow">→</div>
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span>360 días</span>
                        </div>
                        <div class="flow-arrow">→</div>
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span>Beneficios por Smart Contracts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Storytelling -->
    <section class="storytelling">
        <div class="container">
            <div class="story-content">
                <h2>Únete a la Revolución Financiera</h2>
                <div class="story-text">
                    <p>
                        La humanidad está despertando a una <strong>revolución financiera</strong> que cambiará 
                        todo lo que conocemos. Mizton es tu puerta al nuevo paradigma: una 
                        <strong>comunidad global</strong> de personas que apuestan por la tokenización 
                        para blindar sus economías familiares y construir un legado real.
                    </p>
                    <p>
                        Es hora de dejar atrás los modelos antiguos y sumarte a un movimiento 
                        <strong>disruptivo, inclusivo y con un futuro prometedor</strong>. 
                    </p>
                    <div class="story-question">
                        <strong>¿Estás listo para ser pionero?</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Presentación de Oportunidad -->
    <section class="presentation-invitation">
        <div class="container">
            <div class="invitation-content">
                <div class="invitation-visual">
                    <div class="video-preview">
                        <div class="video-icon">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="live-indicator">
                            <span class="live-dot"></span>
                            EN VIVO
                        </div>
                    </div>
                </div>
                <div class="invitation-text">
                    <h2>Descubre la Narrativa que está Cambiando la Economía Mundial</h2>
                    <p class="invitation-description">
                        Únete a nuestra <strong>presentación exclusiva</strong> donde revelamos cómo la 
                        <strong>tokenización de activos del mundo real</strong> está revolucionando las finanzas 
                        globales y cómo puedes ser parte de esta transformación.
                    </p>
                    <div class="invitation-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>Presentaciones diarias en vivo</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-users"></i>
                            <span>Acceso completamente gratuito</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-lightbulb"></i>
                            <span>Conoce la tecnología del futuro</span>
                        </div>
                    </div>
                    <div class="invitation-cta">
                        <a href="meeting.php<?php echo isset($_SESSION['referido']) ? '?ref=' . $_SESSION['referido'] : ''; ?>" class="btn btn-presentation">
                            <i class="fas fa-video"></i>
                            Ver Presentación Ahora
                        </a>
                        <p class="cta-note">
                            <i class="fas fa-info-circle"></i>
                            Descubre por qué miles ya están participando en esta revolución
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RWA Section -->
    <section class="rwa-section">
        <div class="container">
            <div class="section-header">
                <h2>¿Por qué es crucial involucrarse en la tokenización RWA hoy?</h2>
            </div>
            <div class="rwa-content">
                <div class="rwa-text">
                    <p>
                        Vivimos en una era donde la economía global está experimentando un 
                        <strong>cambio sin precedentes</strong>. La tokenización de activos del mundo real (RWA) 
                        representa la evolución natural del dinero, la economía y la inversión, 
                        digitalizando bienes y oportunidades que antes estaban fuera del alcance de la mayoría.
                    </p>
                    <p>
                        Desde propiedades inmobiliarias, metales preciosos, hasta proyectos productivos, 
                        todo ahora puede convertirse en un token que se compra, vende y posee de manera 
                        <strong>segura y transparente</strong> en la blockchain.
                    </p>
                    <div class="rwa-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-democratize"></i>
                            <span>Democratiza el acceso</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-cut"></i>
                            <span>Reduce intermediarios</span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Mayor flexibilidad y menor riesgo</span>
                        </div>
                    </div>
                    <p>
                        Al unirte a Mizton, no sólo adquieres tokens; te integras en una 
                        <strong>comunidad global</strong> que comparte conocimiento, oportunidades y ganancias. 
                        Es una forma de <strong>protección financiera activa</strong>, participativa y 
                        alineada con las tendencias más innovadoras del siglo XXI.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona -->
    <section id="como-funciona" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>¿Cómo Funciona?</h2>
                <p>Un proceso simple y transparente en 4 pasos</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Paquete de Participación</h3>
                        <p>Participa desde un paquete de <strong>$20 USD</strong> y recibe tokens Mizton.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Período de Vesting</h3>
                        <p>Tus tokens quedan en vesting durante <strong>360 días</strong>, período en el que recibirás <strong>ganancias mensuales</strong> distribuidas automáticamente.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Tú Decides</h3>
                        <p>Tras el periodo de vesting, puedes optar por retirar tu aporte inicial y acceder a potenciales bonos adicionales conforme al desempeño comunitario y del contrato inteligente.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Liquidez disponible</h3>
                        <p>Para salir sólo retira tu paquete de tokens y recibe <strong>liquidez disponible</strong> al finalizar el periodo de vesting sujeta a condiciones de la plataforma, del contrato inteligente y la dinámica de la comunidad.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section id="beneficios" class="benefits">
        <div class="container">
            <div class="section-header">
                <h2>Beneficios Exclusivos</h2>
                <p>Ventajas únicas que solo encontrarás en Mizton</p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Opción de solicitar la devolución del 100% del aporte</h3>
                    <p>Tras el periodo de participación, más la posibilidad de bono adicional según los términos del contrato inteligente. Consulta términos y condiciones para más detalles.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3>Ganancias Automáticas</h3>
                    <p>Si continúas con nosotros, obtén ganancias mensuales automáticas mediante contratos inteligentes.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Comunidad Innovadora</h3>
                    <p>Forma parte de la comunidad cripto más innovadora y conecta con pioneros como tú.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Transforma tu Economía</h3>
                    <p>Oportunidad accesible y real para transformar tu economía familiar desde $20 USD.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Bonos Exclusivos</h3>
                    <p>Accede a bonos exclusivos por ser pionero y por referidos que invites a la comunidad.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Educación Financiera</h3>
                    <p>Accede a masterclasses exclusivas y contenido educativo para crecer financieramente.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="unirse" class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¡Únete hoy mismo!</h2>
                <p>Descubre como participar en la nueva economía y cómo beneficiarte de ello.</p>
                <div class="cta-buttons">
                    <a href="#registro" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Quiero Unirme Ahora
                    </a>
                    <a href="#whatsapp" class="btn btn-secondary btn-large">
                        <i class="fab fa-whatsapp"></i>
                        Quiero saber más
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>Testimonios y Comunidad</h2>
                <p class="community-quote">
                    "Ya somos <strong>cientos de pioneros</strong> formando una comunidad sólida preparada para la nueva economía mundial."
                </p>
                <span class="community-signature">- Comunidad Mizton</span>
            </div>
            <!--div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Mizton cambió mi perspectiva sobre las inversiones. Las ganancias mensuales son reales y la comunidad es increíble."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">M</div>
                        <div class="author-info">
                            <span class="author-name">María González</span>
                            <span class="author-role">Pionera Mizton</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"La transparencia de los contratos inteligentes me da total confianza. Es el futuro de las inversiones."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">C</div>
                        <div class="author-info">
                            <span class="author-name">Carlos Ruiz</span>
                            <span class="author-role">Inversor Temprano</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Con solo $20 pude empezar a construir mi futuro financiero. La recuperación garantizada me da tranquilidad."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">A</div>
                        <div class="author-info">
                            <span class="author-name">Ana Martínez</span>
                            <span class="author-role">Miembro Activo</span>
                        </div>
                    </div>
                </div>
            </div-->
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="faq">
        <div class="container">
            <div class="section-header">
                <h2>Preguntas Frecuentes</h2>
                <p>Resolvemos tus dudas más comunes</p>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>¿Cuándo recupero mi participación?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Al final de 360 días si decides no continuar, solicita tu participación más la posibilidad de bono adicional según los términos del contrato inteligente.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>¿Cómo se pagan las ganancias?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Mensualmente y automáticamente, a través de contratos inteligentes transparentes y verificables.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>¿Puedo salir antes?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Experimenta nuestra comunidad al menos 360 días y luego podrás solicitar la recompra de tu(s) membresía(s), entregar tus tokens y recibir liquidez.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>¿Qué pasa si no sé usar cripto?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Te damos soporte paso a paso para tu primera operación y participación. Nuestro equipo te acompaña en todo el proceso.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Educación y Referidos -->
    <section class="education">
        <div class="container">
            <div class="education-content">
                <div class="education-text">
                    <h2>Educación y Referidos</h2>
                    <p>
                        Además con tu membresía, accede a <strong>masterclasses exclusivas</strong>, 
                        contenido educativo y una red activa para crecer en lo financiero y digital.
                    </p>
                    <p>
                        <strong>¿Quieres que otros se unan?</strong> Comparte tu experiencia y 
                        crece junto a tu comunidad.
                    </p>
                </div>
                <div class="education-features">
                    <div class="feature">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Masterclasses Exclusivas</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-book-open"></i>
                        <span>Contenido Educativo</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-network-wired"></i>
                        <span>Red Activa</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-share-alt"></i>
                        <span>Programa de Referidos</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cierre -->
    <section class="closing">
        <div class="container">
            <div class="closing-content">
                <h2>Mizton es comunidad, transparencia y futuro</h2>
                <p>
                    Tú controlas tus aportes mediante <strong>contratos inteligentes públicos</strong>. 
                    La oportunidad para formar parte del cambio está aquí.
                </p>
                <div class="closing-question">
                    <strong>¿Te unes?</strong>
                </div>
                <div class="closing-cta">
                    <a href="#registro" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        Sí, Quiero Unirme Ahora
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="logo.gif" alt="Mizton" class="footer-logo">
                    <span class="footer-brand-text">Mizton</span>
                </div>
                <div class="footer-text">
                    <p>La revolución financiera comienza contigo. Únete a la comunidad preparada para la economía mundial.</p>
                </div>
                <div class="footer-links">
                    <a href="privacidad.php">Política de Privacidad</a>
                    <a href="terminos.php">Términos y Condiciones</a>
                    <a href="mailto:atencion@mizton.cat">Contacto</a>
                </div>
            </div>
            <div class="footer-disclaimer">
                <p class="disclaimer-text">
                    <strong>Disclaimer Legal:</strong> La participación en activos digitales implica riesgos. Consulta los términos y condiciones. Mizton no garantiza rendimientos fijos, los beneficios están sujetos al desempeño de la comunidad y la plataforma.
                </p>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mizton. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="chat-widget-fixed.js"></script>
    <script src="facebook-pixel-events.js"></script>
</body>
</html>
<!-- Test deployment -->
