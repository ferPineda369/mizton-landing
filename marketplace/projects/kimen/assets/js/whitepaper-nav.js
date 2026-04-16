/* ============================================
   KIMEN Whitepaper – Navigation & Scroll Spy
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

  // --- Sidebar toggle for mobile ---
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const sidebar       = document.querySelector('.whitepaper-sidebar');
  const sidebarLinks  = document.querySelectorAll('.sidebar-nav a');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    sidebarLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
          sidebar.classList.remove('open');
        }
      });
    });

    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 1024 &&
          sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // --- Smooth scroll for sidebar anchor links ---
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href.startsWith('#')) {
        e.preventDefault();
        const target = document.getElementById(href.substring(1));
        if (target) {
          const offset = 88;
          const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
          window.scrollTo({ top, behavior: 'smooth' });
        }
      }
    });
  });

  // --- Scroll spy: highlight active section ---
  const sections = document.querySelectorAll('.wp-section');

  const spyObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id');
        sidebarLinks.forEach(link => link.classList.remove('active'));
        const active = document.querySelector(`.sidebar-nav a[href="#${id}"]`);
        if (active) active.classList.add('active');
      }
    });
  }, { root: null, rootMargin: '-100px 0px -60% 0px', threshold: 0 });

  sections.forEach(s => spyObserver.observe(s));

  // --- Back to top button ---
  const backToTop = document.querySelector('.back-to-top');
  if (backToTop) {
    window.addEventListener('scroll', () => {
      backToTop.classList.toggle('visible', window.pageYOffset > 500);
    }, { passive: true });

    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // --- Reading progress bar ---
  const progressBar = document.createElement('div');
  progressBar.style.cssText = `
    position: fixed;
    top: 68px;
    left: 0;
    width: 0%;
    height: 3px;
    background: linear-gradient(90deg, #FFD700, #9B59B6);
    z-index: 1001;
    transition: width 0.1s ease;
  `;
  document.body.appendChild(progressBar);

  window.addEventListener('scroll', () => {
    const scrollable = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled   = (window.pageYOffset / scrollable) * 100;
    progressBar.style.width = scrolled + '%';
  }, { passive: true });

  // --- Copy buttons for code blocks ---
  document.querySelectorAll('pre code').forEach(block => {
    const pre    = block.parentElement;
    const button = document.createElement('button');
    button.textContent = 'Copiar';
    button.style.cssText = `
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      padding: 0.2rem 0.7rem;
      background: rgba(255, 215, 0, 0.15);
      border: 1px solid rgba(255, 215, 0, 0.3);
      border-radius: 4px;
      color: #FFD700;
      font-size: 0.72rem;
      font-family: inherit;
      cursor: pointer;
      transition: all 0.2s;
    `;
    button.addEventListener('mouseenter', () => { button.style.background = 'rgba(255,215,0,0.25)'; });
    button.addEventListener('mouseleave', () => { button.style.background = 'rgba(255,215,0,0.15)'; });
    button.addEventListener('click', () => {
      navigator.clipboard.writeText(block.textContent).then(() => {
        button.textContent = '✓ Copiado';
        setTimeout(() => { button.textContent = 'Copiar'; }, 2000);
      });
    });
    pre.appendChild(button);
  });

  // --- Lucide icons reinit after dynamic content ---
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

});
