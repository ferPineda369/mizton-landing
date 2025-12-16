<?php
// Configurar la cookie de sesión para que sea válida en todos los subdominios
ini_set('session.cookie_domain', '.mizton.cat');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Mizton</title>
    <meta name="description" content="Términos y Condiciones de uso de la plataforma Mizton. Conoce tus derechos y obligaciones como miembro.">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mizton.cat/terminos.php">
    <meta property="og:title" content="Términos y Condiciones - Mizton">
    <meta property="og:description" content="Términos y Condiciones de uso de la plataforma Mizton. Conoce tus derechos y obligaciones como miembro.">
    
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
                <a href="index.php#como-funciona">¿Cómo Funciona?</a>
                <a href="index.php#beneficios">Beneficios</a>
                <a href="index.php#faq">FAQ</a>
                <a href="index.php#unirse" class="cta-nav">Únete Ahora</a>
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
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
            
            <div class="legal-header">
                <h1><i class="fas fa-file-contract"></i> Términos y Condiciones</h1>
                <p class="last-update">Última actualización: 16 de diciembre de 2025</p>
            </div>
            
            <div class="legal-card">
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
                    <li>Crear múltiples cuentas para una misma persona.</li>
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
                    <a href="index.php#contacto">Contacto</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mizton. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
