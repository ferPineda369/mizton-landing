<?php
// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['ref'])) {
    $_SESSION['referido'] = preg_replace('/[^a-z0-9]/', '', strtolower($_GET['ref']));
    // Debug: verificar que la variable se está guardando
    $logFile = __DIR__ . '/landing_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    if (is_writable(dirname($logFile))) {
        file_put_contents($logFile, "[$timestamp] LANDING DEBUG: Referido guardado en sesión: " . $_SESSION['referido'] . "\n", FILE_APPEND);
        file_put_contents($logFile, "[$timestamp] LANDING DEBUG: Session ID: " . session_id() . "\n", FILE_APPEND);
        file_put_contents($logFile, "[$timestamp] LANDING DEBUG: Cookie domain: " . ini_get('session.cookie_domain') . "\n", FILE_APPEND);
    }
}

include 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mizton - La ÚNICA Membresía con Recuperación 100% Garantizada</title>
    <meta name="description" content="Invierte $50 USD en un futuro financiero seguro, innovador y comunitario. Recuperación 100% garantizada + dividendos y bonos.">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/">
    <meta property="og:title" content="Mizton - La ÚNICA Membresía con Recuperación 100% Garantizada">
    <meta property="og:description" content="Invierte $50 USD en un futuro financiero seguro, innovador y comunitario. Recuperación 100% garantizada + dividendos y bonos.">
    <meta property="og:image" content="https://mizton.cat/social-preview.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Mizton">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://mizton.cat/">
    <meta property="twitter:title" content="Mizton - La ÚNICA Membresía con Recuperación 100% Garantizada">
    <meta property="twitter:description" content="Invierte $50 USD en un futuro financiero seguro, innovador y comunitario. Recuperación 100% garantizada + dividendos y bonos.">
    <meta property="twitter:image" content="https://mizton.cat/social-preview.jpg">
    
    <!-- WhatsApp -->
    <meta property="og:locale" content="es_ES">
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="#como-funciona">¿Cómo Funciona?</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#faq">FAQ</a>
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
                Participa desde $50 USD en una propuesta innovadora y comunitaria. Construye junto a nosotros nuevas formas de gestionar tu economía familiar.
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
                        <span class="stat-number">+++</span>
                        <span class="stat-label">Bono opcional según términos</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">$50</span>
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
                    Mientras tanto, disfruta <strong>dividendos mensuales automáticos</strong> gracias a contratos inteligentes.
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
                            <span>Participa con $50+</span>
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
                        <p>Participa desde un paquete de <strong>$50 USD</strong> y recibe tokens Mizton.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Período de Vesting</h3>
                        <p>Tus tokens quedan en vesting durante <strong>360 días</strong>, período en el que recibirás <strong>dividendos mensuales</strong> distribuidos automáticamente.</p>
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
                    <h3>Dividendos Automáticos</h3>
                    <p>Si continúas con nosotros, obtén dividendos mensuales automáticos mediante contratos inteligentes.</p>
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
                    <p>Oportunidad accesible y real para transformar tu economía familiar desde $50 USD.</p>
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
                    "Ya somos <strong>cientos de pioneros</strong> formando una comunidad sólida que transforma la forma de invertir."
                </p>
                <span class="community-signature">- Comunidad Mizton</span>
            </div>
            <!--div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Mizton cambió mi perspectiva sobre las inversiones. Los dividendos mensuales son reales y la comunidad es increíble."</p>
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
                        <p>"Con solo $50 pude empezar a construir mi futuro financiero. La recuperación garantizada me da tranquilidad."</p>
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
                        <h3>¿Cómo se pagan los dividendos?</h3>
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
                    <p>La revolución financiera comienza contigo. Únete a la comunidad que está transformando el futuro de las inversiones.</p>
                </div>
                <div class="footer-links">
                    <a href="#privacidad">Política de Privacidad</a>
                    <a href="#terminos">Términos y Condiciones</a>
                    <a href="#contacto">Contacto</a>
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
</body>
</html>
<!-- Test deployment -->
