/**
 * JavaScript para Landing Pages Internas de Proyectos
 */

$(document).ready(function() {
    
    // ==================== FAQ ACCORDION ====================
    $('.faq-question').on('click', function() {
        const faqItem = $(this).parent('.faq-item');
        
        // Toggle active class
        faqItem.toggleClass('active');
        
        // Cerrar otros FAQs (opcional - comentar si quieres múltiples abiertos)
        // $('.faq-item').not(faqItem).removeClass('active');
    });
    
    // ==================== GALLERY LIGHTBOX ====================
    $('.gallery-item').on('click', function() {
        const imageUrl = $(this).data('image');
        const imageAlt = $(this).find('img').attr('alt');
        
        $('#lightbox-img').attr('src', imageUrl);
        $('#lightbox-caption').text(imageAlt);
        $('#lightbox').fadeIn(300);
        
        // Prevenir scroll del body
        $('body').css('overflow', 'hidden');
    });
    
    $('.lightbox-close, #lightbox').on('click', function(e) {
        if (e.target === this) {
            $('#lightbox').fadeOut(300);
            $('body').css('overflow', 'auto');
        }
    });
    
    // Cerrar lightbox con ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#lightbox').fadeOut(300);
            $('body').css('overflow', 'auto');
        }
    });
    
    // ==================== SMOOTH SCROLL ====================
    $('a[href^="#"]').on('click', function(e) {
        const target = $(this.getAttribute('href'));
        
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });
    
    // ==================== ANIMATIONS ON SCROLL ====================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observar elementos para animación
    document.querySelectorAll('.feature-card, .team-member, .gallery-item, .testimonial-card, .milestone-item').forEach(el => {
        observer.observe(el);
    });
    
    // ==================== PROGRESS BARS ANIMATION ====================
    const progressObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressFill = entry.target.querySelector('.progress-fill');
                if (progressFill) {
                    const width = progressFill.style.width;
                    progressFill.style.width = '0';
                    setTimeout(() => {
                        progressFill.style.width = width;
                    }, 100);
                }
                progressObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    document.querySelectorAll('.milestone-progress, .progress-bar').forEach(el => {
        progressObserver.observe(el);
    });
    
    // ==================== STATS COUNTER ANIMATION ====================
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            // Formatear número
            const formatted = Math.floor(current).toLocaleString();
            element.textContent = formatted;
        }, 16);
    }
    
    // Animar contadores cuando sean visibles
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const value = entry.target.textContent.replace(/[^0-9]/g, '');
                if (value) {
                    animateCounter(entry.target, parseInt(value));
                }
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    document.querySelectorAll('.stat-value, .invest-stat-value').forEach(el => {
        const text = el.textContent;
        // Solo animar si contiene números
        if (/\d/.test(text) && !text.includes('$') && !text.includes('%')) {
            statsObserver.observe(el);
        }
    });
    
});

// ==================== CSS ANIMATIONS ====================
// Agregar estilos de animación dinámicamente
const style = document.createElement('style');
style.textContent = `
    .feature-card,
    .team-member,
    .gallery-item,
    .testimonial-card,
    .milestone-item {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .feature-card.animate-in,
    .team-member.animate-in,
    .gallery-item.animate-in,
    .testimonial-card.animate-in,
    .milestone-item.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Lightbox Styles */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .lightbox-content {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 40px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
        transition: color 0.3s ease;
    }
    
    .lightbox-close:hover {
        color: #ccc;
    }
    
    .lightbox-caption {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        font-size: 1.1rem;
        text-align: center;
        max-width: 80%;
    }
    
    /* Stagger animation delays */
    .feature-card:nth-child(1) { transition-delay: 0.1s; }
    .feature-card:nth-child(2) { transition-delay: 0.2s; }
    .feature-card:nth-child(3) { transition-delay: 0.3s; }
    .feature-card:nth-child(4) { transition-delay: 0.4s; }
    
    .team-member:nth-child(1) { transition-delay: 0.1s; }
    .team-member:nth-child(2) { transition-delay: 0.2s; }
    .team-member:nth-child(3) { transition-delay: 0.3s; }
    .team-member:nth-child(4) { transition-delay: 0.4s; }
    
    .gallery-item:nth-child(1) { transition-delay: 0.05s; }
    .gallery-item:nth-child(2) { transition-delay: 0.1s; }
    .gallery-item:nth-child(3) { transition-delay: 0.15s; }
    .gallery-item:nth-child(4) { transition-delay: 0.2s; }
    .gallery-item:nth-child(5) { transition-delay: 0.25s; }
    .gallery-item:nth-child(6) { transition-delay: 0.3s; }
`;
document.head.appendChild(style);
