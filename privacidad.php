<?php
ini_set('session.cookie_domain', '.mizton.cat');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'lang/loader.php';
$currentLang = getCurrentLang();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('lp.privacy_meta_title') ?></title>
    <meta name="description" content="<?= __('lp.privacy_meta_desc') ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/privacidad.php">
    <meta property="og:title" content="<?= __('lp.privacy_meta_title') ?>">
    <meta property="og:description" content="<?= __('lp.privacy_meta_desc') ?>">
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .legal-content {
            padding: 120px 0 60px;
            background: linear-gradient(135deg, #0a1628 0%, #1a2d4a 100%);
            min-height: 100vh;
        }
        .legal-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .legal-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .legal-header h1 {
            color: #4ade80;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .legal-header .last-update {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .legal-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 40px;
            border: 1px solid rgba(74, 222, 128, 0.1);
        }
        .legal-card h2 {
            color: #4ade80;
            font-size: 1.5rem;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(74, 222, 128, 0.2);
        }
        .legal-card h2:first-child {
            margin-top: 0;
        }
        .legal-card h3 {
            color: #e2e8f0;
            font-size: 1.2rem;
            margin: 20px 0 10px;
        }
        .legal-card p, .legal-card li {
            color: #cbd5e1;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .legal-card ul {
            padding-left: 25px;
            margin-bottom: 20px;
        }
        .legal-card li {
            margin-bottom: 8px;
        }
        .legal-card strong {
            color: #e2e8f0;
        }
        .legal-card a {
            color: #4ade80;
            text-decoration: none;
        }
        .legal-card a:hover {
            text-decoration: underline;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4ade80;
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            transform: translateX(-5px);
        }
        .contact-box {
            background: rgba(74, 222, 128, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid rgba(74, 222, 128, 0.2);
        }
        .contact-box h3 {
            color: #4ade80;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php" style="display: flex; align-items: center; text-decoration: none;">
                    <img src="logo.gif" alt="Mizton" class="logo">
                    <span class="brand-text">Mizton</span>
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php#como-funciona"><?= __('lp.nav_how') ?></a>
                <a href="index.php#beneficios"><?= __('lp.nav_benefits') ?></a>
                <a href="index.php#faq"><?= __('lp.nav_faq') ?></a>
                <a href="index.php#unirse" class="cta-nav"><?= __('lp.nav_join') ?></a>
                <div class="lang-dropdown" id="langDropdown">
                    <button class="lang-dropdown-btn" onclick="document.getElementById('langDropdown').classList.toggle('open')">
                        <?= $currentLang === 'es' ? '🇲🇽 ES' : '🇺🇸 EN' ?>
                        <i class="fas fa-chevron-down lang-chevron"></i>
                    </button>
                    <div class="lang-dropdown-menu">
                        <a href="<?= getLangUrl('es') ?>" class="lang-option<?= $currentLang === 'es' ? ' active' : '' ?>">🇲🇽 Español</a>
                        <a href="<?= getLangUrl('en') ?>" class="lang-option<?= $currentLang === 'en' ? ' active' : '' ?>">🇺🇸 English</a>
                    </div>
                </div>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section class="legal-content">
        <div class="legal-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> <?= __('lp.legal_back') ?>
            </a>
            
            <div class="legal-header">
                <h1><i class="fas fa-shield-alt"></i> <?= __('lp.privacy_h1') ?></h1>
                <p class="last-update"><?= __('lp.terms_last_update') ?></p>
            </div>
            
            <div class="legal-card">
            <?php if ($currentLang === 'en'): ?>
                <h2>1. Introduction</h2>
                <p>At <strong>Mizton</strong> ("we", "our", or "the Platform"), we are committed to protecting the privacy and security of our users' personal data. This Privacy Policy describes how we collect, use, store, and protect your personal information when you use our membership and asset tokenization platform.</p>
                <p>By registering and using our services, you accept the practices described in this policy.</p>

                <h2>2. Information We Collect</h2>
                <h3>2.1 Information provided directly</h3>
                <ul>
                    <li><strong>Registration data:</strong> Full name, email address, phone number (optional), referral code.</li>
                    <li><strong>Profile information:</strong> Profile photo (optional), communication preferences.</li>
                    <li><strong>Financial data:</strong> Cryptocurrency wallet address (for receiving payments and tokens).</li>
                    <li><strong>Communications:</strong> Messages sent through support chat, emails, and inquiries.</li>
                </ul>
                <h3>2.2 Automatically collected information</h3>
                <ul>
                    <li><strong>Usage data:</strong> Pages visited, browsing time, actions taken on the platform.</li>
                    <li><strong>Technical information:</strong> IP address, browser type, operating system, device used.</li>
                    <li><strong>Cookies and similar technologies:</strong> We use cookies to improve user experience and analyze traffic.</li>
                </ul>
                <h3>2.3 Third-party information</h3>
                <ul>
                    <li>Blockchain transaction data (public by nature).</li>
                    <li>Identity verification information when required by applicable regulations.</li>
                </ul>

                <h2>3. Use of Information</h2>
                <p>We use your personal information to:</p>
                <ul>
                    <li><strong>Provide our services:</strong> Manage your account, process memberships, distribute tokens and earnings.</li>
                    <li><strong>Communications:</strong> Send notifications about your account, platform updates, and marketing communications (with your consent).</li>
                    <li><strong>Security:</strong> Prevent fraud, protect platform integrity, and comply with legal obligations.</li>
                    <li><strong>Service improvement:</strong> Analyze platform usage to improve user experience.</li>
                    <li><strong>Legal compliance:</strong> Comply with regulatory requirements and respond to legal requests.</li>
                </ul>

                <h2>4. Information Sharing</h2>
                <p>We do not sell or rent your personal information. We may share data with:</p>
                <ul>
                    <li><strong>Service providers:</strong> Companies that help us operate the platform (payment processors, hosting services, analytics).</li>
                    <li><strong>Blockchain:</strong> Blockchain transactions are public by design. Wallet addresses and transaction amounts are publicly visible.</li>
                    <li><strong>Legal authorities:</strong> When required by law or to protect our legal rights.</li>
                    <li><strong>Corporate transfers:</strong> In the event of a merger, acquisition, or sale of assets.</li>
                </ul>

                <h2>5. Data Security</h2>
                <p>We implement technical and organizational security measures to protect your information:</p>
                <ul>
                    <li>SSL/TLS encryption for all communications.</li>
                    <li>Secure password storage using hashing algorithms.</li>
                    <li>Restricted access to personal data only for authorized personnel.</li>
                    <li>Continuous security monitoring and intrusion detection.</li>
                    <li>Use of audited smart contracts for financial transactions.</li>
                </ul>

                <h2>6. Data Retention</h2>
                <p>We retain your personal information while:</p>
                <ul>
                    <li>Your account is active.</li>
                    <li>It is necessary to provide our services.</li>
                    <li>There are legal retention obligations (generally 5-7 years for financial records).</li>
                    <li>It is necessary to resolve disputes or enforce our agreements.</li>
                </ul>

                <h2>7. Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal data.</li>
                    <li><strong>Rectification:</strong> Correct inaccurate or incomplete data.</li>
                    <li><strong>Erasure:</strong> Request deletion of your data (subject to legal retention obligations).</li>
                    <li><strong>Portability:</strong> Receive your data in a structured and readable format.</li>
                    <li><strong>Objection:</strong> Object to the processing of your data for marketing purposes.</li>
                    <li><strong>Withdrawal of consent:</strong> Withdraw your consent at any time.</li>
                </ul>
                <p>To exercise these rights, contact us through the means indicated at the end of this policy.</p>

                <h2>8. Cookies and Tracking Technologies</h2>
                <p>We use cookies for:</p>
                <ul>
                    <li><strong>Essential cookies:</strong> Necessary for platform operation (session, authentication).</li>
                    <li><strong>Analytics cookies:</strong> To understand how users interact with the platform.</li>
                    <li><strong>Marketing cookies:</strong> To display relevant content (with your consent).</li>
                </ul>
                <p>You can manage your cookie preferences through your browser settings.</p>

                <h2>9. International Transfers</h2>
                <p>Your data may be transferred to and processed in countries other than your own. We ensure that such transfers comply with applicable data protection laws and that adequate safeguards are in place.</p>

                <h2>10. Minors</h2>
                <p>Our services are not directed to individuals under 18 years of age. We do not intentionally collect information from minors. If we discover that we have collected data from a minor, we will delete it immediately.</p>

                <h2>11. Changes to this Policy</h2>
                <p>We may update this Privacy Policy periodically. We will notify you of significant changes through:</p>
                <ul>
                    <li>Notice on the platform.</li>
                    <li>Email to the registered address.</li>
                    <li>Update of the "last updated" date on this page.</li>
                </ul>

                <h2>12. Contact</h2>
                <div class="contact-box">
                    <h3><i class="fas fa-envelope"></i> Questions about privacy?</h3>
                    <p>If you have questions, concerns, or wish to exercise your rights related to your personal data, you can contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:atencion@mizton.cat">atencion@mizton.cat</a></li>
                        <li><strong>Contact form:</strong> Available on our platform</li>
                        <li><strong>Support chat:</strong> Available 24/7 on our main page</li>
                    </ul>
                    <p>We will respond to your request within a maximum of 30 days.</p>
                </div>
            <?php else: ?>
                <h2>1. Introducción</h2>
                <p>En <strong>Mizton</strong> ("nosotros", "nuestro" o "la Plataforma"), nos comprometemos a proteger la privacidad y seguridad de los datos personales de nuestros usuarios. Esta Política de Privacidad describe cómo recopilamos, utilizamos, almacenamos y protegemos su información personal cuando utiliza nuestra plataforma de membresías y tokenización de activos.</p>
                <p>Al registrarse y utilizar nuestros servicios, usted acepta las prácticas descritas en esta política.</p>

                <h2>2. Información que Recopilamos</h2>
                
                <h3>2.1 Información proporcionada directamente</h3>
                <ul>
                    <li><strong>Datos de registro:</strong> Nombre completo, dirección de correo electrónico, número de teléfono (opcional), código de referido.</li>
                    <li><strong>Información de perfil:</strong> Foto de perfil (opcional), preferencias de comunicación.</li>
                    <li><strong>Datos financieros:</strong> Dirección de wallet de criptomonedas (para recibir pagos y tokens).</li>
                    <li><strong>Comunicaciones:</strong> Mensajes enviados a través del chat de soporte, correos electrónicos y consultas.</li>
                </ul>

                <h3>2.2 Información recopilada automáticamente</h3>
                <ul>
                    <li><strong>Datos de uso:</strong> Páginas visitadas, tiempo de navegación, acciones realizadas en la plataforma.</li>
                    <li><strong>Información técnica:</strong> Dirección IP, tipo de navegador, sistema operativo, dispositivo utilizado.</li>
                    <li><strong>Cookies y tecnologías similares:</strong> Utilizamos cookies para mejorar la experiencia del usuario y analizar el tráfico.</li>
                </ul>

                <h3>2.3 Información de terceros</h3>
                <ul>
                    <li>Datos de transacciones en blockchain (públicos por naturaleza).</li>
                    <li>Información de verificación de identidad cuando sea requerida por regulaciones aplicables.</li>
                </ul>

                <h2>3. Uso de la Información</h2>
                <p>Utilizamos su información personal para:</p>
                <ul>
                    <li><strong>Proveer nuestros servicios:</strong> Gestionar su cuenta, procesar membresías, distribuir tokens y ganancias.</li>
                    <li><strong>Comunicaciones:</strong> Enviar notificaciones sobre su cuenta, actualizaciones de la plataforma, y comunicaciones de marketing (con su consentimiento).</li>
                    <li><strong>Seguridad:</strong> Prevenir fraudes, proteger la integridad de la plataforma y cumplir con obligaciones legales.</li>
                    <li><strong>Mejora del servicio:</strong> Analizar el uso de la plataforma para mejorar la experiencia del usuario.</li>
                    <li><strong>Cumplimiento legal:</strong> Cumplir con requisitos regulatorios y responder a solicitudes legales.</li>
                </ul>

                <h2>4. Compartición de Información</h2>
                <p>No vendemos ni alquilamos su información personal. Podemos compartir datos con:</p>
                <ul>
                    <li><strong>Proveedores de servicios:</strong> Empresas que nos ayudan a operar la plataforma (procesadores de pago, servicios de hosting, análisis).</li>
                    <li><strong>Blockchain:</strong> Las transacciones en blockchain son públicas por diseño. Las direcciones de wallet y montos de transacción son visibles públicamente.</li>
                    <li><strong>Autoridades legales:</strong> Cuando sea requerido por ley o para proteger nuestros derechos legales.</li>
                    <li><strong>Transferencias corporativas:</strong> En caso de fusión, adquisición o venta de activos.</li>
                </ul>

                <h2>5. Seguridad de los Datos</h2>
                <p>Implementamos medidas de seguridad técnicas y organizativas para proteger su información:</p>
                <ul>
                    <li>Encriptación SSL/TLS para todas las comunicaciones.</li>
                    <li>Almacenamiento seguro de contraseñas mediante algoritmos de hash.</li>
                    <li>Acceso restringido a datos personales solo a personal autorizado.</li>
                    <li>Monitoreo continuo de seguridad y detección de intrusiones.</li>
                    <li>Uso de contratos inteligentes auditados para transacciones financieras.</li>
                </ul>

                <h2>6. Retención de Datos</h2>
                <p>Conservamos su información personal mientras:</p>
                <ul>
                    <li>Su cuenta esté activa.</li>
                    <li>Sea necesario para proporcionar nuestros servicios.</li>
                    <li>Existan obligaciones legales de retención (generalmente 5-7 años para registros financieros).</li>
                    <li>Sea necesario para resolver disputas o hacer cumplir nuestros acuerdos.</li>
                </ul>

                <h2>7. Sus Derechos</h2>
                <p>Usted tiene derecho a:</p>
                <ul>
                    <li><strong>Acceso:</strong> Solicitar una copia de sus datos personales.</li>
                    <li><strong>Rectificación:</strong> Corregir datos inexactos o incompletos.</li>
                    <li><strong>Eliminación:</strong> Solicitar la eliminación de sus datos (sujeto a obligaciones legales de retención).</li>
                    <li><strong>Portabilidad:</strong> Recibir sus datos en un formato estructurado y legible.</li>
                    <li><strong>Oposición:</strong> Oponerse al procesamiento de sus datos para fines de marketing.</li>
                    <li><strong>Retiro del consentimiento:</strong> Retirar su consentimiento en cualquier momento.</li>
                </ul>
                <p>Para ejercer estos derechos, contáctenos a través de los medios indicados al final de esta política.</p>

                <h2>8. Cookies y Tecnologías de Seguimiento</h2>
                <p>Utilizamos cookies para:</p>
                <ul>
                    <li><strong>Cookies esenciales:</strong> Necesarias para el funcionamiento de la plataforma (sesión, autenticación).</li>
                    <li><strong>Cookies de análisis:</strong> Para entender cómo los usuarios interactúan con la plataforma.</li>
                    <li><strong>Cookies de marketing:</strong> Para mostrar contenido relevante (con su consentimiento).</li>
                </ul>
                <p>Puede gestionar sus preferencias de cookies a través de la configuración de su navegador.</p>

                <h2>9. Transferencias Internacionales</h2>
                <p>Sus datos pueden ser transferidos y procesados en países distintos al suyo. Nos aseguramos de que dichas transferencias cumplan con las leyes de protección de datos aplicables y que existan salvaguardas adecuadas.</p>

                <h2>10. Menores de Edad</h2>
                <p>Nuestros servicios no están dirigidos a menores de 18 años. No recopilamos intencionalmente información de menores. Si descubrimos que hemos recopilado datos de un menor, los eliminaremos inmediatamente.</p>

                <h2>11. Cambios a esta Política</h2>
                <p>Podemos actualizar esta Política de Privacidad periódicamente. Le notificaremos sobre cambios significativos a través de:</p>
                <ul>
                    <li>Aviso en la plataforma.</li>
                    <li>Correo electrónico a la dirección registrada.</li>
                    <li>Actualización de la fecha de "última actualización" en esta página.</li>
                </ul>

                <h2>12. Contacto</h2>
                <div class="contact-box">
                    <h3><i class="fas fa-envelope"></i> ¿Preguntas sobre privacidad?</h3>
                    <p>Si tiene preguntas, inquietudes o desea ejercer sus derechos relacionados con sus datos personales, puede contactarnos:</p>
                    <ul>
                        <li><strong>Correo electrónico:</strong> <a href="mailto:atencion@mizton.cat">atencion@mizton.cat</a></li>
                        <li><strong>Formulario de contacto:</strong> Disponible en nuestra plataforma</li>
                        <li><strong>Chat de soporte:</strong> Disponible 24/7 en nuestra página principal</li>
                    </ul>
                    <p>Responderemos a su solicitud en un plazo máximo de 30 días.</p>
                </div>
            <?php endif; ?>
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
                    <a href="index.php#contacto"><?= __('lp.footer_contact') ?></a>
                </div>
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
</body>
</html>
