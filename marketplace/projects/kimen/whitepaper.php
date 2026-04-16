<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KIMEN Whitepaper – Un Bello Mundo por Extrañar Tokenizado</title>
  <meta name="description" content="Whitepaper completo de KIMEN: tokenización literaria, modelo de regalías, tokenomics, roadmap y análisis de mercado del primer libro tokenizado de Mizton.">

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
        <li><a href="index.php#comprar" class="wp-nav-cta">Comprar KIMEN</a></li>
      </ul>
      <div class="wp-mobile-toggle"><span></span><span></span><span></span></div>
    </div>
  </nav>

  <div class="whitepaper-container">

    <aside class="whitepaper-sidebar">
      <div class="sidebar-header">
        <h2>Whitepaper KIMEN</h2>
        <div class="version">Versión 1.0 | Jun 2026</div>
      </div>
      <nav>
        <ul class="sidebar-nav">
          <li><a href="#resumen" class="active"><span class="section-number">1.</span> Resumen Ejecutivo</a></li>
          <li><a href="#problema"><span class="section-number">2.</span> El Problema</a></li>
          <li><a href="#solucion"><span class="section-number">3.</span> La Solución</a></li>
          <li><a href="#modelo"><span class="section-number">4.</span> Modelo de Negocio</a></li>
          <li><a href="#tokenomics"><span class="section-number">5.</span> Tokenomics KIMEN</a></li>
          <li><a href="#equipo"><span class="section-number">6.</span> Equipo</a></li>
          <li><a href="#mercado"><span class="section-number">7.</span> Análisis de Mercado</a></li>
          <li><a href="#roadmap-detail"><span class="section-number">8.</span> Roadmap</a></li>
          <li><a href="#riesgos"><span class="section-number">9.</span> Riesgos</a></li>
          <li><a href="#conclusion"><span class="section-number">10.</span> Conclusión</a></li>
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
        <a href="index.php#comprar">Comprar</a>
      </div>
    </div>
  </footer>

  <script src="assets/js/whitepaper-nav.js"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
