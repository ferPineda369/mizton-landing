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
    <title><?= __('lp.terms_meta_title') ?></title>
    <meta name="description" content="<?= __('lp.terms_meta_desc') ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/terminos.php">
    <meta property="og:title" content="<?= __('lp.terms_meta_title') ?>">
    <meta property="og:description" content="<?= __('lp.terms_meta_desc') ?>">
    
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
        .legal-card ul, .legal-card ol {
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
        .highlight-box {
            background: rgba(74, 222, 128, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid rgba(74, 222, 128, 0.2);
        }
        .highlight-box.warning {
            background: rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.3);
        }
        .highlight-box.warning h3 {
            color: #fbbf24;
        }
        .highlight-box h3 {
            color: #4ade80;
            margin-top: 0;
            margin-bottom: 10px;
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
        .toc {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .toc h3 {
            color: #4ade80;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .toc ol {
            columns: 2;
            column-gap: 30px;
        }
        .toc li {
            margin-bottom: 5px;
        }
        .toc a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .toc a:hover {
            color: #4ade80;
        }
        @media (max-width: 768px) {
            .toc ol {
                columns: 1;
            }
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
                <h1><i class="fas fa-file-contract"></i> <?= __('lp.terms_h1') ?></h1>
                <p class="last-update"><?= __('lp.terms_last_update') ?></p>
            </div>
            
            <div class="legal-card">
            <?php if ($currentLang === 'en'): ?>
                <div class="toc">
                    <h3><i class="fas fa-list"></i> Table of Contents</h3>
                    <ol>
                        <li><a href="#aceptacion">Acceptance of Terms</a></li>
                        <li><a href="#definiciones">Definitions</a></li>
                        <li><a href="#servicios">Service Description</a></li>
                        <li><a href="#registro">Registration and Account</a></li>
                        <li><a href="#membresias">Memberships and Packages</a></li>
                        <li><a href="#tokens">Corporate Tokens</a></li>
                        <li><a href="#ganancias">Earnings System</a></li>
                        <li><a href="#referidos">Referral Program</a></li>
                        <li><a href="#recuperacion">Investment Recovery</a></li>
                        <li><a href="#obligaciones">User Obligations</a></li>
                        <li><a href="#prohibiciones">Prohibited Conduct</a></li>
                        <li><a href="#riesgos">Risks and Disclaimer</a></li>
                        <li><a href="#propiedad">Intellectual Property</a></li>
                        <li><a href="#modificaciones">Modifications</a></li>
                        <li><a href="#terminacion">Termination</a></li>
                        <li><a href="#ley">Applicable Law</a></li>
                        <li><a href="#contacto">Contact</a></li>
                    </ol>
                </div>

                <h2 id="aceptacion">1. Acceptance of Terms</h2>
                <p>By accessing, registering, or using the <strong>Mizton</strong> platform ("the Platform", "we", "our"), you ("User", "Member", "you") agree to be legally bound by these Terms and Conditions, our <a href="privacidad.php">Privacy Policy</a>, and any additional policies published on the Platform.</p>
                <p>If you do not agree with any of these terms, you must not use our services.</p>

                <h2 id="definiciones">2. Definitions</h2>
                <ul>
                    <li><strong>Platform:</strong> The website mizton.cat and all its subdomains, applications, and related services.</li>
                    <li><strong>Membership:</strong> A participation package that grants access to the benefits of the Mizton community.</li>
                    <li><strong>Corporate Tokens:</strong> Digital assets issued by Mizton that represent participation in the company's global earnings.</li>
                    <li><strong>Wallet:</strong> A cryptocurrency wallet where the user receives tokens and earnings.</li>
                    <li><strong>Vesting:</strong> A maturation period during which tokens are subject to restrictions.</li>
                    <li><strong>Binary Network:</strong> A referral structure organized into two main branches.</li>
                    <li><strong>DCA (Dollar Cost Averaging):</strong> A system of periodic package acquisition.</li>
                </ul>

                <h2 id="servicios">3. Service Description</h2>
                <p>Mizton is a real-world asset (RWA) tokenization platform that offers:</p>
                <ul>
                    <li>Participation memberships in tokenization projects.</li>
                    <li>Corporate tokens representing participation in global earnings.</li>
                    <li>Referral system with a multilevel binary structure.</li>
                    <li>Financial education and blockchain training.</li>
                    <li>Technical support and personalized guidance.</li>
                </ul>

                <h2 id="registro">4. Registration and Account</h2>
                <h3>4.1 Requirements</h3>
                <ul>
                    <li>Be at least 18 years of age.</li>
                    <li>Provide accurate and up-to-date information.</li>
                    <li>Have a valid email address.</li>
                    <li>Be invited by an existing member (referral code).</li>
                </ul>
                <h3>4.2 Account Responsibilities</h3>
                <ul>
                    <li>Maintain the confidentiality of your access credentials.</li>
                    <li>Immediately report any unauthorized use.</li>
                    <li>Be responsible for all activities carried out from your account.</li>
                    <li>Keep your contact information and wallet address up to date.</li>
                </ul>

                <h2 id="membresias">5. Memberships and Packages</h2>
                <h3>5.1 Participation Packages</h3>
                <ul>
                    <li><strong>Value:</strong> $20 USD per participation package.</li>
                    <li><strong>Content:</strong> Each package includes Mizton corporate tokens.</li>
                    <li><strong>Variability:</strong> The number of tokens may vary according to current promotions.</li>
                </ul>
                <h3>5.2 Payment Methods</h3>
                <ul>
                    <li>Cryptocurrencies (USDT, BNB, others subject to availability).</li>
                    <li>Available platform balance.</li>
                    <li>Bank transfers (subject to regional availability).</li>
                </ul>
                <h3>5.3 Vesting Period</h3>
                <p>Acquired tokens are subject to a vesting period of <strong>360 days</strong>, during which:</p>
                <ul>
                    <li>Tokens remain in platform custody.</li>
                    <li>The user receives monthly earnings as applicable.</li>
                    <li>The period may be reduced through the referral program.</li>
                </ul>

                <h2 id="tokens">6. Corporate Tokens</h2>
                <h3>6.1 Nature of Tokens</h3>
                <ul>
                    <li>They are <strong>utility tokens</strong> and <strong>revenue sharing tokens</strong>.</li>
                    <li>They represent participation in Mizton's global earnings.</li>
                    <li>They do not constitute securities, stocks, or regulated financial instruments.</li>
                    <li>Their value is linked to the performance of tokenization projects.</li>
                </ul>
                <h3>6.2 Custody and Transfer</h3>
                <ul>
                    <li>Tokens are stored securely on the blockchain.</li>
                    <li>Transfer to a personal wallet is subject to completion of the vesting period.</li>
                    <li>Mizton is not responsible for losses resulting from improper management of personal wallets.</li>
                </ul>

                <h2 id="ganancias">7. Earnings System</h2>
                <h3>7.1 Global Earnings</h3>
                <ul>
                    <li>Earnings are distributed based on the number of tokens held.</li>
                    <li>Distribution is proportional to the performance of tokenized projects.</li>
                    <li>Payments are made through smart contracts.</li>
                </ul>
                <h3>7.2 Atomic Settlement</h3>
                <ul>
                    <li>Commission payments are processed in a maximum of 7 seconds.</li>
                    <li>Funds are deposited directly into the user's wallet.</li>
                </ul>

                <div class="highlight-box warning">
                    <h3><i class="fas fa-exclamation-triangle"></i> Important Notice</h3>
                    <p>Earnings are not guaranteed and depend on community and tokenization project performance. Past returns do not guarantee future results.</p>
                </div>

                <h2 id="referidos">8. Referral Program</h2>
                <h3>8.1 Multilevel Binary Structure</h3>
                <ul>
                    <li>System of 10 depth levels.</li>
                    <li>$1 USD per package acquired in the structure.</li>
                    <li>No locks or balancing requirements.</li>
                </ul>
                <h3>8.2 Activation Requirements</h3>
                <ul>
                    <li>Maintain at least one active package every 30 days to receive full commissions.</li>
                    <li>Cumulative activation allows extending the collection period.</li>
                </ul>
                <h3>8.3 Vesting Reduction</h3>
                <ul>
                    <li>With 1 direct referral: 300 days.</li>
                    <li>With 2 direct referrals: 240 days.</li>
                    <li>With 3 or more direct referrals: 180 days.</li>
                </ul>

                <h2 id="recuperacion">9. Investment Recovery</h2>
                <h3>9.1 Recovery Guarantee</h3>
                <p>Upon completion of the vesting period, the user may request recovery of <strong>100% of their initial investment</strong>, subject to:</p>
                <ul>
                    <li>Having completed the applicable vesting period.</li>
                    <li>Returning corporate tokens to Mizton.</li>
                    <li>Having no outstanding obligations with the platform.</li>
                </ul>
                <h3>9.2 Request Process</h3>
                <ul>
                    <li>The request is made through the user panel.</li>
                    <li>Processing may take up to 30 business days.</li>
                    <li>Payment is made in the agreed cryptocurrency or method.</li>
                </ul>

                <h2 id="obligaciones">10. User Obligations</h2>
                <p>The user agrees to:</p>
                <ul>
                    <li>Provide accurate information and keep it up to date.</li>
                    <li>Comply with all applicable laws and regulations.</li>
                    <li>Not use the platform for illegal activities.</li>
                    <li>Respect the rights of other users and Mizton.</li>
                    <li>Keep credentials and wallet secure.</li>
                    <li>Report any suspicious activity or vulnerability.</li>
                </ul>

                <h2 id="prohibiciones">11. Prohibited Conduct</h2>
                <p>The following is strictly prohibited:</p>
                <ul>
                    <li>Providing false or misleading information.</li>
                    <li>Fraudulently manipulating the referral system.</li>
                    <li>Money laundering or illicit financing activities.</li>
                    <li>Attempting to hack, breach, or compromise platform security.</li>
                    <li>Defaming, harassing, or harming other users or Mizton.</li>
                    <li>Using unauthorized bots, scripts, or automations.</li>
                    <li>Violating intellectual property rights.</li>
                </ul>

                <h2 id="riesgos">12. Risks and Disclaimer</h2>
                <div class="highlight-box warning">
                    <h3><i class="fas fa-exclamation-circle"></i> Risk Statement</h3>
                    <p>Participation in digital assets and cryptocurrencies involves significant risks. By using Mizton, you acknowledge and accept:</p>
                </div>
                <ul>
                    <li><strong>Volatility:</strong> The value of digital assets can fluctuate significantly.</li>
                    <li><strong>Technological risk:</strong> Failures in blockchain, smart contracts, or infrastructure.</li>
                    <li><strong>Regulatory risk:</strong> Changes in laws that may affect services.</li>
                    <li><strong>Market risk:</strong> Economic conditions affecting projects.</li>
                    <li><strong>Access loss:</strong> Loss of credentials or wallet access.</li>
                </ul>
                <p>Mizton does not guarantee specific returns and is not responsible for losses resulting from:</p>
                <ul>
                    <li>User investment decisions.</li>
                    <li>Cryptocurrency market fluctuations.</li>
                    <li>Mismanagement of wallets or credentials by the user.</li>
                    <li>Force majeure events or circumstances beyond our control.</li>
                </ul>

                <h2 id="propiedad">13. Intellectual Property</h2>
                <ul>
                    <li>All rights to the Mizton brand, logos, designs, and content are the exclusive property of Mizton.</li>
                    <li>The user does not acquire any intellectual property rights by using the platform.</li>
                    <li>Reproduction, distribution, or modification without express authorization is prohibited.</li>
                </ul>

                <h2 id="modificaciones">14. Modifications to Terms</h2>
                <p>Mizton reserves the right to modify these Terms at any time. Modifications will:</p>
                <ul>
                    <li>Be notified at least 15 days in advance.</li>
                    <li>Be communicated by email and/or notice on the platform.</li>
                    <li>Continued use after the effective date constitutes acceptance.</li>
                </ul>

                <h2 id="terminacion">15. Termination</h2>
                <h3>15.1 By the User</h3>
                <p>The user may request account termination at any time, subject to:</p>
                <ul>
                    <li>Completion of the vesting period for fund recovery.</li>
                    <li>Settlement of outstanding obligations.</li>
                </ul>
                <h3>15.2 By Mizton</h3>
                <p>Mizton may suspend or terminate accounts for:</p>
                <ul>
                    <li>Violation of these Terms.</li>
                    <li>Fraudulent or illegal activities.</li>
                    <li>Request from competent authorities.</li>
                    <li>Prolonged inactivity (more than 24 months).</li>
                </ul>

                <h2 id="ley">16. Applicable Law and Jurisdiction</h2>
                <ul>
                    <li>These Terms are governed by the applicable laws of the jurisdiction where Mizton operates.</li>
                    <li>Any dispute will be resolved preferably through mediation or arbitration.</li>
                    <li>In the event of litigation, the parties submit to the competent courts.</li>
                </ul>

                <h2 id="contacto">17. Contact</h2>
                <div class="contact-box">
                    <h3><i class="fas fa-headset"></i> Need help?</h3>
                    <p>For inquiries about these Terms and Conditions or any aspect of our services:</p>
                    <ul>
                        <li><strong>Email:</strong> <a href="mailto:atencion@mizton.cat">atencion@mizton.cat</a></li>
                        <li><strong>Support chat:</strong> Available 24/7 on our main page</li>
                        <li><strong>Live presentations:</strong> Monday to Friday, 10:00 AM (Mexico City time)</li>
                    </ul>
                </div>

                <div class="highlight-box">
                    <h3><i class="fas fa-check-circle"></i> Acceptance</h3>
                    <p>By registering and using the Mizton platform, you confirm that you have read, understood, and accepted these Terms and Conditions in their entirety.</p>
                </div>
            <?php else: ?>
                <div class="toc">
                    <h3><i class="fas fa-list"></i> Índice</h3>
                    <ol>
                        <li><a href="#aceptacion">Aceptación de los Términos</a></li>
                        <li><a href="#definiciones">Definiciones</a></li>
                        <li><a href="#servicios">Descripción de los Servicios</a></li>
                        <li><a href="#registro">Registro y Cuenta</a></li>
                        <li><a href="#membresias">Membresías y Paquetes</a></li>
                        <li><a href="#tokens">Tokens Corporativos</a></li>
                        <li><a href="#ganancias">Sistema de Ganancias</a></li>
                        <li><a href="#referidos">Programa de Referidos</a></li>
                        <li><a href="#recuperacion">Recuperación de Inversión</a></li>
                        <li><a href="#obligaciones">Obligaciones del Usuario</a></li>
                        <li><a href="#prohibiciones">Conductas Prohibidas</a></li>
                        <li><a href="#riesgos">Riesgos y Exención</a></li>
                        <li><a href="#propiedad">Propiedad Intelectual</a></li>
                        <li><a href="#modificaciones">Modificaciones</a></li>
                        <li><a href="#terminacion">Terminación</a></li>
                        <li><a href="#ley">Ley Aplicable</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ol>
                </div>

                <h2 id="aceptacion">1. Aceptación de los Términos</h2>
                <p>Al acceder, registrarse o utilizar la plataforma <strong>Mizton</strong> ("la Plataforma", "nosotros", "nuestro"), usted ("Usuario", "Miembro", "usted") acepta estar legalmente vinculado por estos Términos y Condiciones, nuestra <a href="privacidad.php">Política de Privacidad</a> y cualquier política adicional publicada en la Plataforma.</p>
                <p>Si no está de acuerdo con alguno de estos términos, no debe utilizar nuestros servicios.</p>

                <h2 id="definiciones">2. Definiciones</h2>
                <ul>
                    <li><strong>Plataforma:</strong> El sitio web mizton.cat y todos sus subdominios, aplicaciones y servicios relacionados.</li>
                    <li><strong>Membresía:</strong> Paquete de participación que otorga acceso a los beneficios de la comunidad Mizton.</li>
                    <li><strong>Tokens Corporativos:</strong> Activos digitales emitidos por Mizton que representan participación en las ganancias globales de la compañía.</li>
                    <li><strong>Wallet:</strong> Billetera digital de criptomonedas donde el usuario recibe sus tokens y ganancias.</li>
                    <li><strong>Vesting:</strong> Período de maduración durante el cual los tokens están sujetos a restricciones.</li>
                    <li><strong>Red Binaria:</strong> Estructura de referidos organizada en dos ramas principales.</li>
                    <li><strong>DCA (Dollar Cost Averaging):</strong> Sistema de adquisición periódica de paquetes.</li>
                </ul>

                <h2 id="servicios">3. Descripción de los Servicios</h2>
                <p>Mizton es una plataforma de tokenización de activos del mundo real (RWA) que ofrece:</p>
                <ul>
                    <li>Membresías de participación en proyectos de tokenización.</li>
                    <li>Tokens corporativos que representan participación en ganancias globales.</li>
                    <li>Sistema de referidos con estructura multinivel binaria.</li>
                    <li>Educación financiera y capacitación en blockchain.</li>
                    <li>Soporte técnico y acompañamiento personalizado.</li>
                </ul>

                <h2 id="registro">4. Registro y Cuenta</h2>
                <h3>4.1 Requisitos</h3>
                <ul>
                    <li>Ser mayor de 18 años.</li>
                    <li>Proporcionar información veraz y actualizada.</li>
                    <li>Contar con una dirección de correo electrónico válida.</li>
                    <li>Ser invitado por un miembro existente (código de referido).</li>
                </ul>
                
                <h3>4.2 Responsabilidades de la Cuenta</h3>
                <ul>
                    <li>Mantener la confidencialidad de sus credenciales de acceso.</li>
                    <li>Notificar inmediatamente cualquier uso no autorizado.</li>
                    <li>Ser responsable de todas las actividades realizadas desde su cuenta.</li>
                    <li>Mantener actualizada su información de contacto y wallet.</li>
                </ul>

                <h2 id="membresias">5. Membresías y Paquetes</h2>
                <h3>5.1 Paquetes de Participación</h3>
                <ul>
                    <li><strong>Valor:</strong> $20 USD por paquete de participación.</li>
                    <li><strong>Contenido:</strong> Cada paquete incluye tokens corporativos Mizton.</li>
                    <li><strong>Variabilidad:</strong> La cantidad de tokens puede variar según promociones vigentes.</li>
                </ul>

                <h3>5.2 Métodos de Pago</h3>
                <ul>
                    <li>Criptomonedas (USDT, BNB, otras según disponibilidad).</li>
                    <li>Saldo disponible en la plataforma.</li>
                    <li>Transferencias bancarias (según disponibilidad regional).</li>
                </ul>

                <h3>5.3 Período de Vesting</h3>
                <p>Los tokens adquiridos están sujetos a un período de vesting de <strong>360 días</strong>, durante el cual:</p>
                <ul>
                    <li>Los tokens permanecen en custodia de la plataforma.</li>
                    <li>El usuario recibe ganancias mensuales según corresponda.</li>
                    <li>El período puede reducirse mediante el programa de referidos.</li>
                </ul>

                <h2 id="tokens">6. Tokens Corporativos</h2>
                <h3>6.1 Naturaleza de los Tokens</h3>
                <ul>
                    <li>Son <strong>utility tokens</strong> y <strong>revenue sharing tokens</strong>.</li>
                    <li>Representan participación en las ganancias globales de Mizton.</li>
                    <li>No constituyen valores, acciones ni instrumentos financieros regulados.</li>
                    <li>Su valor está vinculado al desempeño de los proyectos de tokenización.</li>
                </ul>

                <h3>6.2 Custodia y Transferencia</h3>
                <ul>
                    <li>Los tokens se almacenan en la blockchain de forma segura.</li>
                    <li>La transferencia a wallet personal está sujeta al cumplimiento del período de vesting.</li>
                    <li>Mizton no se responsabiliza por pérdidas derivadas del mal manejo de wallets personales.</li>
                </ul>

                <h2 id="ganancias">7. Sistema de Ganancias</h2>
                <h3>7.1 Ganancias Globales</h3>
                <ul>
                    <li>Las ganancias se distribuyen según la cantidad de tokens poseídos.</li>
                    <li>La distribución es proporcional al desempeño de los proyectos tokenizados.</li>
                    <li>Los pagos se realizan mediante contratos inteligentes.</li>
                </ul>

                <h3>7.2 Liquidación Atómica</h3>
                <ul>
                    <li>Los pagos de comisiones se procesan en un máximo de 7 segundos.</li>
                    <li>Los fondos se depositan directamente en la wallet del usuario.</li>
                </ul>

                <div class="highlight-box warning">
                    <h3><i class="fas fa-exclamation-triangle"></i> Aviso Importante</h3>
                    <p>Las ganancias no están garantizadas y dependen del desempeño de la comunidad y los proyectos de tokenización. Los rendimientos pasados no garantizan resultados futuros.</p>
                </div>

                <h2 id="referidos">8. Programa de Referidos</h2>
                <h3>8.1 Estructura Multinivel Binaria</h3>
                <ul>
                    <li>Sistema de 10 niveles de profundidad.</li>
                    <li>$1 USD por cada paquete adquirido en la estructura.</li>
                    <li>Sin candados ni requisitos de balanceo.</li>
                </ul>

                <h3>8.2 Requisitos de Activación</h3>
                <ul>
                    <li>Mantener al menos un paquete activo cada 30 días para cobrar comisiones completas.</li>
                    <li>La activación acumulativa permite extender el período de cobro.</li>
                </ul>

                <h3>8.3 Reducción de Vesting</h3>
                <ul>
                    <li>Con 1 referido directo: 300 días.</li>
                    <li>Con 2 referidos directos: 240 días.</li>
                    <li>Con 3 o más referidos directos: 180 días.</li>
                </ul>

                <h2 id="recuperacion">9. Recuperación de Inversión</h2>
                <h3>9.1 Garantía de Recuperación</h3>
                <p>Al finalizar el período de vesting, el usuario puede solicitar la recuperación del <strong>100% de su inversión inicial</strong>, sujeto a:</p>
                <ul>
                    <li>Haber completado el período de vesting aplicable.</li>
                    <li>Devolver los tokens corporativos a Mizton.</li>
                    <li>No tener obligaciones pendientes con la plataforma.</li>
                </ul>

                <h3>9.2 Proceso de Solicitud</h3>
                <ul>
                    <li>La solicitud se realiza a través del panel de usuario.</li>
                    <li>El procesamiento puede tomar hasta 30 días hábiles.</li>
                    <li>El pago se realiza en la criptomoneda o método acordado.</li>
                </ul>

                <h2 id="obligaciones">10. Obligaciones del Usuario</h2>
                <p>El usuario se compromete a:</p>
                <ul>
                    <li>Proporcionar información veraz y mantenerla actualizada.</li>
                    <li>Cumplir con todas las leyes y regulaciones aplicables.</li>
                    <li>No utilizar la plataforma para actividades ilegales.</li>
                    <li>Respetar los derechos de otros usuarios y de Mizton.</li>
                    <li>Mantener seguras sus credenciales y wallet.</li>
                    <li>Reportar cualquier actividad sospechosa o vulnerabilidad.</li>
                </ul>

                <h2 id="prohibiciones">11. Conductas Prohibidas</h2>
                <p>Está estrictamente prohibido:</p>
                <ul>
                    <li>Proporcionar información falsa o engañosa.</li>
                    <li>Manipular el sistema de referidos de forma fraudulenta.</li>
                    <li>Realizar actividades de lavado de dinero o financiamiento ilícito.</li>
                    <li>Intentar hackear, vulnerar o comprometer la seguridad de la plataforma.</li>
                    <li>Difamar, acosar o perjudicar a otros usuarios o a Mizton.</li>
                    <li>Utilizar bots, scripts o automatizaciones no autorizadas.</li>
                    <li>Violar derechos de propiedad intelectual.</li>
                </ul>

                <h2 id="riesgos">12. Riesgos y Exención de Responsabilidad</h2>
                <div class="highlight-box warning">
                    <h3><i class="fas fa-exclamation-circle"></i> Declaración de Riesgos</h3>
                    <p>La participación en activos digitales y criptomonedas implica riesgos significativos. Al utilizar Mizton, usted reconoce y acepta:</p>
                </div>
                <ul>
                    <li><strong>Volatilidad:</strong> El valor de los activos digitales puede fluctuar significativamente.</li>
                    <li><strong>Riesgo tecnológico:</strong> Fallos en blockchain, contratos inteligentes o infraestructura.</li>
                    <li><strong>Riesgo regulatorio:</strong> Cambios en leyes que puedan afectar los servicios.</li>
                    <li><strong>Riesgo de mercado:</strong> Condiciones económicas que afecten los proyectos.</li>
                    <li><strong>Pérdida de acceso:</strong> Pérdida de credenciales o acceso a wallet.</li>
                </ul>
                <p>Mizton no garantiza rendimientos específicos ni se responsabiliza por pérdidas derivadas de:</p>
                <ul>
                    <li>Decisiones de inversión del usuario.</li>
                    <li>Fluctuaciones del mercado de criptomonedas.</li>
                    <li>Mal manejo de wallets o credenciales por parte del usuario.</li>
                    <li>Eventos de fuerza mayor o circunstancias fuera de nuestro control.</li>
                </ul>

                <h2 id="propiedad">13. Propiedad Intelectual</h2>
                <ul>
                    <li>Todos los derechos sobre la marca Mizton, logotipos, diseños y contenido son propiedad exclusiva de Mizton.</li>
                    <li>El usuario no adquiere ningún derecho de propiedad intelectual por el uso de la plataforma.</li>
                    <li>Está prohibida la reproducción, distribución o modificación sin autorización expresa.</li>
                </ul>

                <h2 id="modificaciones">14. Modificaciones a los Términos</h2>
                <p>Mizton se reserva el derecho de modificar estos Términos en cualquier momento. Las modificaciones:</p>
                <ul>
                    <li>Serán notificadas con al menos 15 días de anticipación.</li>
                    <li>Se comunicarán por correo electrónico y/o aviso en la plataforma.</li>
                    <li>El uso continuado después de la fecha efectiva constituye aceptación.</li>
                </ul>

                <h2 id="terminacion">15. Terminación</h2>
                <h3>15.1 Por el Usuario</h3>
                <p>El usuario puede solicitar la terminación de su cuenta en cualquier momento, sujeto a:</p>
                <ul>
                    <li>Cumplimiento del período de vesting para recuperación de fondos.</li>
                    <li>Liquidación de obligaciones pendientes.</li>
                </ul>

                <h3>15.2 Por Mizton</h3>
                <p>Mizton puede suspender o terminar cuentas por:</p>
                <ul>
                    <li>Violación de estos Términos.</li>
                    <li>Actividades fraudulentas o ilegales.</li>
                    <li>Solicitud de autoridades competentes.</li>
                    <li>Inactividad prolongada (más de 24 meses).</li>
                </ul>

                <h2 id="ley">16. Ley Aplicable y Jurisdicción</h2>
                <ul>
                    <li>Estos Términos se rigen por las leyes aplicables en la jurisdicción donde opera Mizton.</li>
                    <li>Cualquier disputa se resolverá preferentemente mediante mediación o arbitraje.</li>
                    <li>En caso de litigio, las partes se someten a los tribunales competentes.</li>
                </ul>

                <h2 id="contacto">17. Contacto</h2>
                <div class="contact-box">
                    <h3><i class="fas fa-headset"></i> ¿Necesitas ayuda?</h3>
                    <p>Para consultas sobre estos Términos y Condiciones o cualquier aspecto de nuestros servicios:</p>
                    <ul>
                        <li><strong>Correo electrónico:</strong> <a href="mailto:atencion@mizton.cat">atencion@mizton.cat</a></li>
                        <li><strong>Chat de soporte:</strong> Disponible 24/7 en nuestra página principal</li>
                        <li><strong>Presentaciones en vivo:</strong> Lunes a viernes, 10:00 AM (hora Ciudad de México)</li>
                    </ul>
                </div>

                <div class="highlight-box">
                    <h3><i class="fas fa-check-circle"></i> Aceptación</h3>
                    <p>Al registrarse y utilizar la plataforma Mizton, usted confirma que ha leído, entendido y aceptado estos Términos y Condiciones en su totalidad.</p>
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
