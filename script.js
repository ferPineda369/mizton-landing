// Mizton Landing Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Navegación móvil
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }
    
    // Navegación suave
    const navLinksElements = document.querySelectorAll('a[href^="#"]');
    navLinksElements.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
            
            // Cerrar menú móvil si está abierto
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    });
    
    // Efecto parallax en hero
    const hero = document.querySelector('.hero');
    const heroParticles = document.querySelector('.hero-particles');
    
    if (hero && heroParticles) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            heroParticles.style.transform = `translateY(${rate}px)`;
        });
    }
    
    // Animaciones al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Solo animar si no se ha animado antes
                if (!entry.target.classList.contains('animated')) {
                    entry.target.classList.add('fade-in-up', 'animated');
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    
                    // Animación especial para estadísticas
                    if (entry.target.classList.contains('stat-number')) {
                        animateNumber(entry.target);
                    }
                }
            }
        });
    }, observerOptions);
    
    // Observar elementos para animaciones
    const elementsToAnimate = document.querySelectorAll('.section-header, .step, .benefit-card, .testimonial-card, .stat-number');
    elementsToAnimate.forEach((el, index) => {
        // Solo aplicar animaciones si el elemento no está ya visible
        if (!el.classList.contains('fade-in-up')) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease-out';
            el.dataset.delay = index * 100;
        }
        observer.observe(el);
    });
    
    // Animación de números
    function animateNumber(element) {
        const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        const duration = 2000;
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (element.textContent.includes('%')) {
                element.textContent = Math.floor(current) + '%';
            } else if (element.textContent.includes('$')) {
                element.textContent = '$' + Math.floor(current);
            } else {
                element.textContent = Math.floor(current) + '%';
            }
        }, 16);
    }
    
    // FAQ Accordion
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');
            
            // Cerrar todos los FAQs
            faqItems.forEach(faq => {
                faq.classList.remove('active');
            });
            
            // Abrir el clickeado si no estaba activo
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
    
    // Efecto de typing en el hero title (deshabilitado para evitar mostrar HTML)
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        heroTitle.style.opacity = '1';
        // Removido el efecto typewriter que causaba mostrar etiquetas HTML
    }
    
    // Partículas flotantes en el hero
    function createFloatingParticles() {
        const hero = document.querySelector('.hero');
        if (!hero) return;
        
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'floating-particle';
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                background: rgba(116, 198, 157, ${Math.random() * 0.5 + 0.2});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: float ${Math.random() * 10 + 10}s linear infinite;
                z-index: 1;
            `;
            hero.appendChild(particle);
        }
    }
    
    createFloatingParticles();
    
    // Efecto hover mejorado para botones
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Cambio de navbar al hacer scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(27, 67, 50, 0.98)';
                navbar.style.backdropFilter = 'blur(15px)';
                navbar.style.boxShadow = '0 2px 20px rgba(27, 67, 50, 0.3)';
            } else {
                navbar.style.background = 'rgba(27, 67, 50, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
                navbar.style.boxShadow = 'none';
            }
        });
    }
    
    // Contador de visitantes simulado
    function updateVisitorCounter() {
        const counter = document.querySelector('.visitor-counter');
        if (counter) {
            const baseCount = 1247;
            const randomAdd = Math.floor(Math.random() * 10);
            counter.textContent = baseCount + randomAdd;
        }
    }
    
    // Actualizar contador cada 30 segundos
    setInterval(updateVisitorCounter, 30000);
    
    // Efecto de pulsación en elementos importantes
    const pulseElements = document.querySelectorAll('.btn-primary, .stat-number');
    pulseElements.forEach(element => {
        setInterval(() => {
            element.style.animation = 'pulse 0.5s ease-in-out';
            setTimeout(() => {
                element.style.animation = '';
            }, 500);
        }, 5000);
    });
    
    // Validación básica de formularios (si se añaden más adelante)
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#dc3545';
                    input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                } else {
                    input.style.borderColor = '#40916C';
                    input.style.boxShadow = '0 0 0 0.2rem rgba(64, 145, 108, 0.25)';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
            }
        });
    });
    
    // Lazy loading para imágenes
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Efecto de escritura para testimonios (simplificado)
    const testimonials = document.querySelectorAll('.testimonial-content p');
    testimonials.forEach((testimonial) => {
        testimonial.style.opacity = '1';
        testimonial.style.transform = 'translateX(0)';
        testimonial.style.transition = 'all 0.6s ease-out';
    });
    
    // Función para manejar CTAs
    function handleCTAClicks() {
        const ctaButtons = document.querySelectorAll('a[href="#unirse"], a[href="#registro"]');
        console.log('Botones CTA encontrados:', ctaButtons.length);
        
        ctaButtons.forEach(button => {
            console.log('Configurando evento para botón:', button);
            button.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Click en botón CTA detectado');
                console.log('MIZTON_CONFIG:', window.MIZTON_CONFIG);
                
                // Usar configuración del entorno
                if (window.MIZTON_CONFIG && window.MIZTON_CONFIG.register_url) {
                    let registerUrl = window.MIZTON_CONFIG.register_url;
                    console.log('URL base:', registerUrl);
                    
                    // Si hay referido en la configuración, añadirlo como parámetro URL
                    if (window.MIZTON_CONFIG.referido) {
                        const separator = registerUrl.includes('?') ? '&' : '?';
                        registerUrl += separator + 'ref=' + encodeURIComponent(window.MIZTON_CONFIG.referido);
                        console.log('URL con referido:', registerUrl);
                    } else {
                        console.log('No hay referido en configuración');
                    }
                    
                    console.log('Abriendo URL:', registerUrl);
                    window.open(registerUrl, '_blank');
                } else {
                    console.log('No hay configuración, usando fallback');
                    // Fallback
                    window.open('/panel/register.php', '_blank');
                }
            });
        });
        
        const whatsappButtons = document.querySelectorAll('a[href="#whatsapp"]');
        whatsappButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Usar número de WhatsApp de la configuración
                const whatsappNumber = window.MIZTON_CONFIG?.whatsapp_number || '522215695942';
                const message = encodeURIComponent('¡Hola! Me interesa conocer más sobre Mizton y su membresía garantizada.');
                const whatsappURL = `https://wa.me/${whatsappNumber}?text=${message}`;
                
                window.open(whatsappURL, '_blank');
            });
        });
    }
    
    handleCTAClicks();
    
    // Añadir CSS para animaciones adicionales
    const additionalCSS = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .floating-particle {
            pointer-events: none;
        }
        
        @keyframes float {
            0% { transform: translateY(0px) translateX(0px) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px) rotate(360deg); opacity: 0; }
        }
        
        .nav-links.active {
            display: flex;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(27, 67, 50, 0.98);
            flex-direction: column;
            padding: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
        
        .lazy {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .lazy.loaded {
            opacity: 1;
        }
    `;
    
    const style = document.createElement('style');
    style.textContent = additionalCSS;
    document.head.appendChild(style);
    
    console.log('Mizton Landing Page cargada correctamente ✅');
    
    // Manejar códigos de referido al cargar la página
    handleReferralCode();
});

// Función para manejar códigos de referido
function handleReferralCode() {
    // Obtener código de referido de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const referralCode = urlParams.get('ref');
    
    if (referralCode) {
        // Mostrar información del referido
        fetchReferralInfo(referralCode);
    }
}

// Función para obtener información del referido
async function fetchReferralInfo(referralCode) {
    try {
        const response = await fetch(`api/referral-info.php?ref=${encodeURIComponent(referralCode)}`);
        const data = await response.json();
        
        if (data.success) {
            displayReferralInfo(data.data);
        } else {
            console.log('Código de referido no válido:', data.message);
        }
    } catch (error) {
        console.error('Error obteniendo información del referido:', error);
    }
}

// Función para mostrar información del referido
function displayReferralInfo(referralData) {
    const referralInfo = document.getElementById('referral-info');
    const referrerName = document.getElementById('referrer-name');
    const referrerType = document.getElementById('referrer-type');
    const bonusPercentage = document.getElementById('bonus-percentage');
    
    if (referralInfo && referrerName && referrerType && bonusPercentage) {
        referrerName.textContent = referralData.referrer.name;
        referrerType.textContent = referralData.referrer.founder_type;
        bonusPercentage.textContent = referralData.stats.first_level_bonus;
        
        // OCULTAR la información del referido (no mostrar el cuadro)
        referralInfo.style.display = 'none';
        
        // Actualizar CTAs para incluir el código de referido
        updateCTAsWithReferral(referralData.referral_code, referralData.contact.whatsapp_number);
    }
}

// Función para actualizar CTAs con código de referido
function updateCTAsWithReferral(referralCode, whatsappNumber) {
    const ctaButtons = document.querySelectorAll('a[href="#unirse"], a[href="#registro"]');
    ctaButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Construir URL con código de referido
            let registerUrl = '';
            if (window.MIZTON_CONFIG && window.MIZTON_CONFIG.register_url) {
                registerUrl = window.MIZTON_CONFIG.register_url + '?ref=' + encodeURIComponent(referralCode);
            } else {
                registerUrl = '/panel/register.php?ref=' + encodeURIComponent(referralCode);
            }
            
            window.open(registerUrl, '_blank');
        });
    });
    
    // Actualizar TODOS los botones de WhatsApp con el número correcto
    console.log('🔄 Actualizando botones de WhatsApp con número:', whatsappNumber);
    
    // Buscar todos los botones que pueden ser de WhatsApp
    const allButtons = document.querySelectorAll('a, button');
    let whatsappButtonsFound = 0;
    
    allButtons.forEach(button => {
        const text = button.textContent.toLowerCase();
        const href = button.getAttribute('href') || '';
        
        // Detectar botones de WhatsApp por texto o href
        if (text.includes('whatsapp') || text.includes('contactar') || text.includes('saber más') || 
            text.includes('quiero saber') || href.includes('wa.me') || href.includes('whatsapp')) {
            
            whatsappButtonsFound++;
            console.log('📱 Botón WhatsApp encontrado:', button.textContent.trim());
            
            // Remover eventos anteriores clonando el elemento
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const message = encodeURIComponent('¡Hola! Me interesa saber más sobre Mizton y su membresía garantizada.');
                const whatsappURL = `https://wa.me/${whatsappNumber}?text=${message}`;
                
                console.log('📱 Abriendo WhatsApp:', whatsappURL);
                window.open(whatsappURL, '_blank');
            });
        }
    });
    
    console.log(`✅ ${whatsappButtonsFound} botones de WhatsApp actualizados con número: ${whatsappNumber}`);
}
