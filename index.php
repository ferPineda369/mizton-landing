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

require_once 'lang/loader.php';
$currentLang = getCurrentLang();

include 'config.php';
?>
<!DOCTYPE html>
<html lang="<?= __('lp.html_lang') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('lp.meta_title') ?></title>
    <meta name="description" content="<?= __('lp.meta_desc') ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/">
    <meta property="og:title" content="<?= __('lp.meta_title') ?>">
    <meta property="og:description" content="<?= __('lp.meta_desc') ?>">
    <meta property="og:image" content="https://mizton.cat/social-preview.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Mizton">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://mizton.cat/">
    <meta property="twitter:title" content="<?= __('lp.meta_title') ?>">
    <meta property="twitter:description" content="<?= __('lp.meta_desc') ?>">
    <meta property="twitter:image" content="https://mizton.cat/social-preview.jpg">
    
    <!-- WhatsApp -->
    <meta property="og:locale" content="<?= __('lp.og_locale') ?>">
    
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
                <a href="#como-funciona"><?= __('lp.nav_how') ?></a>
                <a href="#beneficios"><?= __('lp.nav_benefits') ?></a>
                <a href="#faq"><?= __('lp.nav_faq') ?></a>
                <a href="news/" target="_blank"><?= __('lp.nav_news') ?></a>
                <a href="meeting.php<?php echo isset($_SESSION['referido']) ? '?ref=' . $_SESSION['referido'] : ''; ?>" class="meeting-nav">
                    <i class="fas fa-video"></i> <?= __('lp.nav_presentation') ?>
                </a>
                <div class="lang-dropdown" id="langDropdown">
                    <button class="lang-dropdown-btn" onclick="document.getElementById('langDropdown').classList.toggle('open')">
                        <?= $currentLang === 'es' ? '🇲🇽 ES' : '🇺🇸 EN' ?>
                        <i class="fas fa-chevron-down lang-chevron"></i>
                    </button>
                    <div class="lang-dropdown-menu">
                        <a href="<?= getLangUrl('es') ?>" class="lang-option<?= $currentLang === 'es' ? ' active' : '' ?>">
                            🇲🇽 Español
                        </a>
                        <a href="<?= getLangUrl('en') ?>" class="lang-option<?= $currentLang === 'en' ? ' active' : '' ?>">
                            🇺🇸 English
                        </a>
                    </div>
                </div>
                <a href="#unirse" class="cta-nav"><?= __('lp.nav_join') ?></a>
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
                    <span class="hero-title-main"><?= __('lp.hero_title_main') ?></span>
                    <span class="highlight"><?= __('lp.hero_title_sub') ?></span>
                </h1>
                <p class="hero-subtitle">
                <?= __('lp.hero_subtitle') ?>
                </p>
                
                <!-- Información del referido (se muestra si hay código válido) -->
                <div id="referral-info" class="referral-info" style="display: none;">
                    <div class="referral-card">
                        <div class="referral-header">
                            <i class="fas fa-user-friends"></i>
                            <span><?= __('lp.hero_invited_by') ?></span>
                        </div>
                        <div class="referral-content">
                            <h4 id="referrer-name"><?= __('lp.hero_loading') ?></h4>
                            <p id="referrer-type"><?= __('lp.hero_member_type') ?></p>
                        </div>
                    </div>
                </div>
                <div class="hero-ctas">
                    <a href="#unirse" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        <?= __('lp.hero_btn_join') ?>
                    </a>
                    <a href="#whatsapp" class="btn btn-secondary">
                        <i class="fab fa-whatsapp"></i>
                        <?= __('lp.hero_btn_more') ?>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">100%</span>
                        <span class="stat-label"><?= __('lp.stat1_label') ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">976+</span>
                        <span class="stat-label"><?= __('lp.stat2_label') ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">$20</span>
                        <span class="stat-label"><?= __('lp.stat3_label') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Propuesta de Valor -->
    <section class="value-proposition">
        <div class="container">
            <div class="section-header">
                <h2><?= __('lp.value_title') ?></h2>
            </div>
            <div class="value-content">
                <div class="value-text">
                    <p class="value-main">
                    <?= __('lp.value_main') ?>
                    </p>
                    <p class="value-secondary">
                        <?= __('lp.value_secondary') ?>
                    </p>
                </div>
                <div class="value-visual">
                    <div class="investment-flow">
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <span><?= __('lp.flow_step1') ?></span>
                        </div>
                        <div class="flow-arrow">→</div>
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span><?= __('lp.flow_step2') ?></span>
                        </div>
                        <div class="flow-arrow">→</div>
                        <div class="flow-step">
                            <div class="step-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span><?= __('lp.flow_step3') ?></span>
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
                <h2><?= __('lp.story_title') ?></h2>
                <div class="story-text">
                    <p><?= __('lp.story_p1') ?></p>
                    <p><?= __('lp.story_p2') ?></p>
                    <div class="story-question">
                        <strong><?= __('lp.story_question') ?></strong>
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
                            <?= __('lp.pres_live') ?>
                        </div>
                    </div>
                </div>
                <div class="invitation-text">
                    <h2><?= __('lp.pres_title') ?></h2>
                    <p class="invitation-description"><?= __('lp.pres_desc') ?></p>
                    <div class="invitation-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-calendar-check"></i>
                            <span><?= __('lp.pres_hi1') ?></span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-users"></i>
                            <span><?= __('lp.pres_hi2') ?></span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-lightbulb"></i>
                            <span><?= __('lp.pres_hi3') ?></span>
                        </div>
                    </div>
                    <div class="invitation-cta">
                        <a href="meeting.php<?php echo isset($_SESSION['referido']) ? '?ref=' . $_SESSION['referido'] : ''; ?>" class="btn btn-presentation">
                            <i class="fas fa-video"></i>
                            <?= __('lp.pres_btn') ?>
                        </a>
                        <p class="cta-note">
                            <i class="fas fa-info-circle"></i>
                            <?= __('lp.pres_note') ?>
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
                <h2><?= __('lp.rwa_title') ?></h2>
            </div>
            <div class="rwa-content">
                <div class="rwa-text">
                    <p><?= __('lp.rwa_p1') ?></p>
                    <p><?= __('lp.rwa_p2') ?></p>
                    <div class="rwa-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-democratize"></i>
                            <span><?= __('lp.rwa_b1') ?></span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-cut"></i>
                            <span><?= __('lp.rwa_b2') ?></span>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-shield-alt"></i>
                            <span><?= __('lp.rwa_b3') ?></span>
                        </div>
                    </div>
                    <p><?= __('lp.rwa_p3') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona -->
    <section id="como-funciona" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2><?= __('lp.how_title') ?></h2>
                <p><?= __('lp.how_subtitle') ?></p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3><?= __('lp.how_step1_title') ?></h3>
                        <p><?= __('lp.how_step1_text') ?></p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3><?= __('lp.how_step2_title') ?></h3>
                        <p><?= __('lp.how_step2_text') ?></p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3><?= __('lp.how_step3_title') ?></h3>
                        <p><?= __('lp.how_step3_text') ?></p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3><?= __('lp.how_step4_title') ?></h3>
                        <p><?= __('lp.how_step4_text') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section id="beneficios" class="benefits">
        <div class="container">
            <div class="section-header">
                <h2><?= __('lp.ben_title') ?></h2>
                <p><?= __('lp.ben_subtitle') ?></p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3><?= __('lp.ben1_title') ?></h3>
                    <p><?= __('lp.ben1_text') ?></p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-coins"></i></div>
                    <h3><?= __('lp.ben2_title') ?></h3>
                    <p><?= __('lp.ben2_text') ?></p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-users"></i></div>
                    <h3><?= __('lp.ben3_title') ?></h3>
                    <p><?= __('lp.ben3_text') ?></p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-home"></i></div>
                    <h3><?= __('lp.ben4_title') ?></h3>
                    <p><?= __('lp.ben4_text') ?></p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-star"></i></div>
                    <h3><?= __('lp.ben5_title') ?></h3>
                    <p><?= __('lp.ben5_text') ?></p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3><?= __('lp.ben6_title') ?></h3>
                    <p><?= __('lp.ben6_text') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="unirse" class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2><?= __('lp.cta_title') ?></h2>
                <p><?= __('lp.cta_text') ?></p>
                <div class="cta-buttons">
                    <a href="#registro" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        <?= __('lp.cta_btn_join') ?>
                    </a>
                    <a href="#whatsapp" class="btn btn-secondary btn-large">
                        <i class="fab fa-whatsapp"></i>
                        <?= __('lp.cta_btn_more') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2><?= __('lp.testi_title') ?></h2>
                <p class="community-quote"><?= __('lp.testi_quote') ?></p>
                <span class="community-signature"><?= __('lp.testi_sign') ?></span>
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
                <h2><?= __('lp.faq_title') ?></h2>
                <p><?= __('lp.faq_subtitle') ?></p>
            </div>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= __('lp.faq1_q') ?></h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?= __('lp.faq1_a') ?></p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= __('lp.faq2_q') ?></h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?= __('lp.faq2_a') ?></p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= __('lp.faq3_q') ?></h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?= __('lp.faq3_a') ?></p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?= __('lp.faq4_q') ?></h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p><?= __('lp.faq4_a') ?></p>
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
                    <h2><?= __('lp.edu_title') ?></h2>
                    <p><?= __('lp.edu_p1') ?></p>
                    <p><?= __('lp.edu_p2') ?></p>
                </div>
                <div class="education-features">
                    <div class="feature">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span><?= __('lp.edu_f1') ?></span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-book-open"></i>
                        <span><?= __('lp.edu_f2') ?></span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-network-wired"></i>
                        <span><?= __('lp.edu_f3') ?></span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-share-alt"></i>
                        <span><?= __('lp.edu_f4') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cierre -->
    <section class="closing">
        <div class="container">
            <div class="closing-content">
                <h2><?= __('lp.closing_title') ?></h2>
                <p><?= __('lp.closing_text') ?></p>
                <div class="closing-question">
                    <strong><?= __('lp.closing_question') ?></strong>
                </div>
                <div class="closing-cta">
                    <a href="#registro" class="btn btn-primary btn-large">
                        <i class="fas fa-rocket"></i>
                        <?= __('lp.closing_btn') ?>
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
                    <p><?= __('lp.footer_tagline') ?></p>
                </div>
                <div class="footer-links">
                    <a href="privacidad.php"><?= __('lp.footer_privacy') ?></a>
                    <a href="terminos.php"><?= __('lp.footer_terms') ?></a>
                    <a href="mailto:atencion@mizton.cat"><?= __('lp.footer_contact') ?></a>
                </div>
            </div>
            <div class="footer-disclaimer">
                <p class="disclaimer-text"><?= __('lp.footer_disclaimer') ?></p>
            </div>
            <div class="footer-bottom">
                <p><?= __('lp.footer_copy') ?></p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('langDropdown');
        if (dd && !dd.contains(e.target)) dd.classList.remove('open');
    });
    </script>
    <script src="script.js"></script>
    <script src="chat-widget-fixed.js"></script>
    <script src="facebook-pixel-events.js"></script>
</body>
</html>
<!-- Test deployment -->
