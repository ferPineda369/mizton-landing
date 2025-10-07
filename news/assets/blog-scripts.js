/**
 * Mizton Blog - JavaScript Moderno
 * Funcionalidades interactivas y dinámicas
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeBlog();
});

function initializeBlog() {
    console.log('🚀 Mizton Blog inicializado');
    
    // Inicializar componentes
    initMobileMenu();
    initNewsletterForm();
    initScrollAnimations();
    initReadingProgress();
    initSmoothScroll();
    
    // Tracking de Facebook Pixel para blog
    trackBlogPageView();
}

// Menú móvil
function initMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (toggle && navLinks) {
        toggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            toggle.classList.toggle('active');
        });
        
        // Cerrar menú al hacer clic en un enlace
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                toggle.classList.remove('active');
            });
        });
    }
}

// Formulario de newsletter
function initNewsletterForm() {
    const form = document.getElementById('newsletter-form');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = form.querySelector('input[type="email"]').value;
            const button = form.querySelector('button');
            const originalText = button.innerHTML;
            
            // Estado de carga
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suscribiendo...';
            button.disabled = true;
            
            try {
                const response = await fetch('api/newsletter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email: email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('¡Suscripción exitosa! Gracias por unirte.', 'success');
                    form.reset();
                    
                    // Tracking de conversión
                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'Subscribe', {
                            content_name: 'Blog Newsletter',
                            value: 1,
                            currency: 'USD'
                        });
                    }
                } else {
                    showNotification(data.message || 'Error al suscribir. Inténtalo de nuevo.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error de conexión. Inténtalo de nuevo.', 'error');
            }
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Animaciones de scroll
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    // Observar elementos
    const elementsToAnimate = document.querySelectorAll('.post-card, .featured-card, .section-header');
    elementsToAnimate.forEach(el => observer.observe(el));
}

// Barra de progreso de lectura
function initReadingProgress() {
    // Solo en páginas de posts individuales
    if (document.querySelector('.post-content')) {
        const progressBar = createProgressBar();
        updateReadingProgress(progressBar);
        
        window.addEventListener('scroll', () => updateReadingProgress(progressBar));
    }
}

function createProgressBar() {
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    progressBar.innerHTML = '<div class="reading-progress-bar"></div>';
    
    // Estilos inline para la barra de progreso
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: rgba(27, 67, 50, 0.1);
        z-index: 1000;
    `;
    
    const bar = progressBar.querySelector('.reading-progress-bar');
    bar.style.cssText = `
        height: 100%;
        background: linear-gradient(90deg, #40916C, #52B788);
        width: 0%;
        transition: width 0.3s ease;
    `;
    
    document.body.appendChild(progressBar);
    return bar;
}

function updateReadingProgress(progressBar) {
    const article = document.querySelector('.post-content');
    if (!article) return;
    
    const articleTop = article.offsetTop;
    const articleHeight = article.offsetHeight;
    const windowHeight = window.innerHeight;
    const scrollTop = window.scrollY;
    
    const progress = Math.max(0, Math.min(100, 
        ((scrollTop - articleTop + windowHeight) / articleHeight) * 100
    ));
    
    progressBar.style.width = progress + '%';
}

// Scroll suave
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Cargar más posts
async function loadMorePosts() {
    const button = document.querySelector('.load-more-btn');
    const container = document.querySelector('.posts-container');
    
    if (!button || !container) return;
    
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    button.disabled = true;
    
    try {
        const offset = container.children.length;
        const response = await fetch(`api/load-posts.php?offset=${offset}&limit=6`);
        const data = await response.json();
        
        if (data.success && data.posts.length > 0) {
            data.posts.forEach(post => {
                const postElement = createPostElement(post);
                container.appendChild(postElement);
            });
            
            // Animar nuevos posts
            const newPosts = container.querySelectorAll('.post-card:not(.fade-in-up)');
            newPosts.forEach((post, index) => {
                setTimeout(() => {
                    post.classList.add('fade-in-up');
                }, index * 100);
            });
            
            // Ocultar botón si no hay más posts
            if (data.posts.length < 6) {
                button.style.display = 'none';
            }
        } else {
            button.style.display = 'none';
            showNotification('No hay más artículos para cargar.', 'info');
        }
    } catch (error) {
        console.error('Error cargando posts:', error);
        showNotification('Error al cargar más artículos.', 'error');
    }
    
    button.innerHTML = originalText;
    button.disabled = false;
}

// Crear elemento de post
function createPostElement(post) {
    const article = document.createElement('article');
    article.className = 'post-card';
    
    const tagsHtml = post.tags.map(tag => `<span class="tag">${tag}</span>`).join('');
    
    article.innerHTML = `
        <div class="post-image">
            <img src="${post.image}" alt="${post.title}">
            <div class="post-category">${post.category}</div>
        </div>
        <div class="post-content">
            <div class="post-meta">
                <span class="date">${formatDate(post.published_at || post.created_at)}</span>
                <span class="read-time">${post.read_time} min</span>
            </div>
            <h3 class="post-title">
                <a href="post.php?slug=${post.slug}">${post.title}</a>
            </h3>
            <p class="post-excerpt">${post.excerpt}</p>
            <div class="post-footer">
                <a href="post.php?slug=${post.slug}" class="read-more-link">Leer más</a>
                <div class="post-tags">${tagsHtml}</div>
            </div>
        </div>
    `;
    
    return article;
}

// Formatear fecha
function formatDate(dateString) {
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
                   'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const date = new Date(dateString);
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

// Sistema de notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const colors = {
        success: '#52B788',
        error: '#dc3545',
        info: '#40916C',
        warning: '#ffc107'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 350px;
        font-weight: 500;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remover
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Tracking de Facebook Pixel
function trackBlogPageView() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: 'Blog Page View',
            content_category: 'Blog',
            value: 1,
            currency: 'USD'
        });
        console.log('📊 Blog page view tracked');
    }
}

// Tracking de lectura de posts
function trackPostRead(postTitle, category) {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: postTitle,
            content_category: category,
            content_type: 'blog_post',
            value: 1,
            currency: 'USD'
        });
        console.log('📖 Post read tracked:', postTitle);
    }
}

// Compartir artículo - copia al portapapeles
function sharePost(url, title) {
    // Usar URL base si está disponible, sino usar la URL pasada
    let baseUrl = window.basePostUrl || url;
    let shareUrl = baseUrl;
    
    // Si hay código de referido, agregarlo al path
    if (window.userReferralCode && window.userReferralCode.length === 6) {
        shareUrl = baseUrl + '/' + window.userReferralCode;
    }
    
    // Construir mensaje para compartir
    const shareMessage = `${title}\n\n${shareUrl}`;
    
    // Copiar al portapapeles
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(shareMessage).then(function() {
            showNotification('¡Artículo copiado al portapapeles! Ahora puedes pegarlo en la red social que prefieras.', 'success');
        }).catch(function() {
            fallbackCopyToClipboard(shareMessage);
        });
    } else {
        fallbackCopyToClipboard(shareMessage);
    }
    
    // Tracking
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Share', {
            content_name: title,
            method: 'clipboard',
            referral_code: window.userReferralCode || 'none'
        });
    }
}

// Función fallback para copiar al portapapeles
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('¡Artículo copiado al portapapeles! Ahora puedes pegarlo en la red social que prefieras.', 'success');
    } catch (err) {
        showNotification('No se pudo copiar automáticamente. Selecciona y copia el texto manualmente.', 'error');
        // Mostrar el texto en una alerta para copiado manual
        alert(`Copia este texto:\n\n${text}`);
    } finally {
        document.body.removeChild(textArea);
    }
}

// Búsqueda en tiempo real
function initSearch() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    
    if (searchInput && searchResults) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query, searchResults);
            }, 300);
        });
    }
}

async function performSearch(query, resultsContainer) {
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.results.length > 0) {
            const resultsHtml = data.results.map(post => `
                <div class="search-result">
                    <h4><a href="post.php?slug=${post.slug}">${post.title}</a></h4>
                    <p>${post.excerpt}</p>
                    <span class="search-meta">${post.category} • ${formatDate(post.published_at || post.created_at)}</span>
                </div>
            `).join('');
            
            resultsContainer.innerHTML = resultsHtml;
            resultsContainer.style.display = 'block';
        } else {
            resultsContainer.innerHTML = '<div class="no-results">No se encontraron resultados</div>';
            resultsContainer.style.display = 'block';
        }
    } catch (error) {
        console.error('Error en búsqueda:', error);
        resultsContainer.innerHTML = '<div class="search-error">Error en la búsqueda</div>';
        resultsContainer.style.display = 'block';
    }
}

// Exportar funciones globales
window.loadMorePosts = loadMorePosts;
window.sharePost = sharePost;
window.trackPostRead = trackPostRead;
