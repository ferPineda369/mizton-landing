<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mizton (MZT) — Whitepaper</title>
  <meta name="description" content="Whitepaper oficial de Mizton (MZT). Plataforma comunitaria de tokenización de activos del mundo real (RWA).">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/whitepaper-styles.css">
</head>
<body>

  <!-- Grid Background -->
  <div class="grid-bg"></div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="nav-container">
      <a href="#" class="nav-logo">
        <span class="logo-miz">MIZ</span><span class="logo-ton">TON</span>
        <span class="logo-badge">MZT</span>
      </a>
      <ul class="nav-links">
        <li><a href="#aviso">Whitepaper</a></li>
        <li><a href="#tokenomics">Tokenomics</a></li>
        <li><a href="#mercado">Mercado</a></li>
        <li><a href="#roadmap">Roadmap</a></li>
      </ul>
      <div class="mobile-toggle" id="mobileToggle">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <!-- Whitepaper Layout -->
  <div class="whitepaper-container">

    <!-- Sidebar -->
    <aside class="whitepaper-sidebar">
      <div class="sidebar-header">
        <h2>Whitepaper MZT</h2>
        <span class="version">Versión 1.1 · Abril 2026</span>
      </div>
      <ul class="sidebar-nav">
        <li><a href="#aviso"><span class="section-number">01</span> Aviso Importante</a></li>
        <li><a href="#resumen"><span class="section-number">02</span> Resumen Ejecutivo</a></li>
        <li><a href="#problema"><span class="section-number">03</span> El Problema</a></li>
        <li><a href="#solucion"><span class="section-number">04</span> La Solución</a></li>
        <li><a href="#modelo"><span class="section-number">05</span> Modelo Operativo</a></li>
        <li><a href="#tokenomics"><span class="section-number">06</span> Tokenomics MZT</a></li>
        <li><a href="#equipo"><span class="section-number">07</span> Equipo</a></li>
        <li><a href="#mercado"><span class="section-number">08</span> Contexto de Mercado</a></li>
        <li><a href="#roadmap"><span class="section-number">09</span> Roadmap</a></li>
        <li><a href="#riesgos"><span class="section-number">10</span> Riesgos</a></li>
        <li><a href="#conclusion"><span class="section-number">11</span> Conclusión</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="whitepaper-content">
      <?php include 'whitepaper-content.php'; ?>
    </main>

  </div>

  <!-- Mobile sidebar toggle -->
  <button class="sidebar-toggle" aria-label="Toggle sidebar">
    <i data-lucide="list" style="width:24px;height:24px;"></i>
  </button>

  <!-- Back to top -->
  <button class="back-to-top" aria-label="Volver arriba">
    <i data-lucide="arrow-up" style="width:24px;height:24px;"></i>
  </button>

  <!-- Footer -->
  <footer class="footer" style="margin-left:280px;">
    <div class="footer-content">
      <div class="footer-left">
        <p>&copy; 2026 Mizton. Todos los derechos reservados.</p>
        <p style="font-size:0.75rem;margin-top:0.25rem;color:var(--text-muted);">Este documento es exclusivamente informativo. No constituye oferta de inversión.</p>
      </div>
      <div class="footer-links">
        <a href="#">Términos</a>
        <a href="#">Privacidad</a>
        <a href="#">Contacto</a>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
  <script src="assets/js/whitepaper-nav.js"></script>

</body>
</html>
