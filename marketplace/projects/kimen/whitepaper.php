<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KIMEN Whitepaper – Un Bello Mundo por Extrañar Tokenizado</title>
  <meta name="description" content="Whitepaper KIMEN v1.1: token de utilidad cultural, ecosistema literario digital, emisión y supply, roadmap revisado. Primer libro tokenizado de la saga.">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <link rel="stylesheet" href="assets/css/whitepaper-styles.css">
</head>
<body>

  <div class="stars-bg-wp"></div>

  <nav class="wp-navbar">
    <div class="wp-nav-container">
      <a href="index.php" class="wp-nav-logo">
        <span class="logo-k">K</span><span class="logo-imen">IMEN</span>
        <span class="logo-badge-wp">TOKEN</span>
      </a>
      <ul class="wp-nav-links">
        <li><a href="index.php">Inicio</a></li>
        <li><a href="index.php#tokenomics">Tokenomics</a></li>
        <li><a href="index.php#roadmap">Roadmap</a></li>
        <li><a href="whitepaper.php" class="wp-nav-active">Whitepaper</a></li>
        <li><a href="index.php#obtener" class="wp-nav-cta">Obtener KIMEN</a></li>
      </ul>
      <div class="wp-mobile-toggle"><span></span><span></span><span></span></div>
    </div>
  </nav>

  <div class="whitepaper-container">

    <aside class="whitepaper-sidebar">
      <div class="sidebar-header">
        <h2>Whitepaper KIMEN</h2>
        <div class="version">Versión 1.1 | Jun 2026</div>
      </div>
      <nav>
        <ul class="sidebar-nav">
          <li><a href="#aviso" class="active"><span class="section-number">1.</span> Aviso Importante</a></li>
          <li><a href="#resumen"><span class="section-number">2.</span> Resumen Ejecutivo</a></li>
          <li><a href="#contexto"><span class="section-number">3.</span> Contexto del Proyecto</a></li>
          <li><a href="#objetivo"><span class="section-number">4.</span> Objetivo del Ecosistema</a></li>
          <li><a href="#naturaleza"><span class="section-number">5.</span> Naturaleza del Token</a></li>
          <li><a href="#arquitectura"><span class="section-number">6.</span> Arquitectura Conceptual</a></li>
          <li><a href="#emision"><span class="section-number">7.</span> Emisión y Supply</a></li>
          <li><a href="#utilidades"><span class="section-number">8.</span> Utilidades</a></li>
          <li><a href="#mecanismos"><span class="section-number">9.</span> Mecanismos Futuros</a></li>
          <li><a href="#roadmap-detail"><span class="section-number">10.</span> Roadmap</a></li>
          <li><a href="#riesgos"><span class="section-number">11.</span> Riesgos</a></li>
          <li><a href="#marco"><span class="section-number">12.</span> Marco Interpretativo</a></li>
          <li><a href="#conclusion"><span class="section-number">13.</span> Conclusión</a></li>
          <li><a href="#disclaimer"><span class="section-number">14.</span> Disclaimer Final</a></li>
        </ul>
      </nav>
    </aside>

    <main class="whitepaper-content">
      <?php include 'whitepaper-content.php'; ?>
    </main>

  </div>

  <button class="sidebar-toggle" aria-label="Abrir menú">
    <i data-lucide="menu" style="width:24px;height:24px;"></i>
  </button>

  <button class="back-to-top" aria-label="Volver arriba">
    <i data-lucide="arrow-up" style="width:24px;height:24px;"></i>
  </button>

  <footer class="wp-footer">
    <div class="wp-footer-content">
      <div class="wp-footer-left">
        <p>&copy; <?php echo date('Y'); ?> KIMEN Token — Powered by Mizton</p>
      </div>
      <div class="wp-footer-links">
        <a href="index.php">Inicio</a>
        <a href="whitepaper.php">Whitepaper</a>
        <a href="index.php#legal">Disclaimer Legal</a>
        <a href="index.php#obtener">Obtener</a>
      </div>
    </div>
  </footer>

  <script src="assets/js/whitepaper-nav.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
