/* ============================================
   MZT Whitepaper – Navigation & Scroll Spy
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

  // --- Sidebar toggle for mobile ---
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.whitepaper-sidebar');
  const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking a link (mobile)
    sidebarLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
          sidebar.classList.remove('open');
        }
      });
    });

    // Close sidebar when clicking outside (mobile)
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 1024 && 
          sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) && 
          !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // --- Smooth scroll for anchor links ---
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href.startsWith('#')) {
        e.preventDefault();
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
          const offset = 90;
          const elementPosition = targetElement.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - offset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      }
    });
  });

  // --- Scroll spy: highlight active section ---
  const sections = document.querySelectorAll('.wp-section');
  
  const observerOptions = {
    root: null,
    rootMargin: '-100px 0px -60% 0px',
    threshold: 0
  };

  const observerCallback = (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.getAttribute('id');
        
        // Remove active from all links
        sidebarLinks.forEach(link => link.classList.remove('active'));
        
        // Add active to current section link
        const activeLink = document.querySelector(`.sidebar-nav a[href="#${id}"]`);
        if (activeLink) {
          activeLink.classList.add('active');
        }
      }
    });
  };

  const observer = new IntersectionObserver(observerCallback, observerOptions);
  sections.forEach(section => observer.observe(section));

  // --- Back to top button ---
  const backToTop = document.querySelector('.back-to-top');
  
  if (backToTop) {
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 500) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    }, { passive: true });

    backToTop.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // --- Progress indicator ---
  const createProgressBar = () => {
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
      position: fixed;
      top: 70px;
      left: 0;
      width: 0%;
      height: 3px;
      background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
      z-index: 1001;
      transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
      const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = (window.pageYOffset / windowHeight) * 100;
      progressBar.style.width = scrolled + '%';
    }, { passive: true });
  };

  createProgressBar();

  // --- Copy code blocks ---
  const codeBlocks = document.querySelectorAll('pre code');
  
  codeBlocks.forEach(block => {
    const pre = block.parentElement;
    const button = document.createElement('button');
    button.textContent = 'Copiar';
    button.style.cssText = `
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: rgba(6, 182, 212, 0.2);
      border: 1px solid rgba(6, 182, 212, 0.3);
      border-radius: 4px;
      color: var(--accent-cyan);
      font-size: 0.75rem;
      cursor: pointer;
      transition: all 0.3s;
    `;

    button.addEventListener('mouseenter', () => {
      button.style.background = 'rgba(6, 182, 212, 0.3)';
    });

    button.addEventListener('mouseleave', () => {
      button.style.background = 'rgba(6, 182, 212, 0.2)';
    });

    button.addEventListener('click', () => {
      const text = block.textContent;
      navigator.clipboard.writeText(text).then(() => {
        button.textContent = '✓ Copiado';
        setTimeout(() => {
          button.textContent = 'Copiar';
        }, 2000);
      });
    });

    pre.style.position = 'relative';
    pre.appendChild(button);
  });

  // --- Print functionality ---
  const addPrintButton = () => {
    const header = document.querySelector('.wp-header');
    if (header) {
      const printBtn = document.createElement('button');
      printBtn.innerHTML = '<i data-lucide="printer" style="width:18px;height:18px;"></i> Imprimir';
      printBtn.className = 'btn-secondary';
      printBtn.style.marginTop = '1rem';
      printBtn.addEventListener('click', () => window.print());
      header.appendChild(printBtn);
      
      // Reinitialize lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    }
  };

  addPrintButton();

});
