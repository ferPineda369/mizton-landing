/**
 * Scripts específicos para posts individuales
 * Funcionalidades adicionales para la página de post
 */

document.addEventListener('DOMContentLoaded', function() {
    initializePost();
});

function initializePost() {
    console.log('📖 Post page inicializado');
    
    // Inicializar componentes específicos del post
    generateTableOfContents();
    initSidebarNewsletter();
    initScrollSpy();
    initCopyCodeBlocks();
    initImageLightbox();
}

// Generar tabla de contenidos automáticamente
function generateTableOfContents() {
    const content = document.querySelector('.post-content');
    const tocContainer = document.getElementById('table-of-contents');
    
    if (!content || !tocContainer) return;
    
    const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
    
    if (headings.length === 0) {
        tocContainer.innerHTML = '<p style="color: #6c757d; font-size: 0.875rem;">No hay encabezados en este artículo.</p>';
        return;
    }
    
    const tocList = document.createElement('ul');
    tocList.style.cssText = 'list-style: none; padding: 0; margin: 0;';
    
    headings.forEach((heading, index) => {
        // Crear ID único para el encabezado
        const headingId = `heading-${index}`;
        heading.id = headingId;
        
        // Crear elemento de la tabla de contenidos
        const tocItem = document.createElement('li');
        const tocLink = document.createElement('a');
        
        tocLink.href = `#${headingId}`;
        tocLink.textContent = heading.textContent;
        tocLink.style.cssText = `
            display: block;
            padding: 4px 0;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s ease;
            padding-left: ${(parseInt(heading.tagName.charAt(1)) - 1) * 15}px;
        `;
        
        tocLink.addEventListener('click', function(e) {
            e.preventDefault();
            heading.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Actualizar enlace activo
            updateActiveTocLink(tocLink);
        });
        
        tocItem.appendChild(tocLink);
        tocList.appendChild(tocItem);
    });
    
    tocContainer.appendChild(tocList);
}

// Actualizar enlace activo en tabla de contenidos
function updateActiveTocLink(activeLink) {
    const tocLinks = document.querySelectorAll('#table-of-contents a');
    tocLinks.forEach(link => {
        link.style.color = '#6c757d';
        link.style.fontWeight = 'normal';
    });
    
    activeLink.style.color = '#40916C';
    activeLink.style.fontWeight = '500';
}

// Newsletter del sidebar
function initSidebarNewsletter() {
    const sidebarForm = document.getElementById('sidebar-newsletter-form');
    
    if (sidebarForm) {
        sidebarForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            const button = this.querySelector('button');
            const originalText = button.innerHTML;
            
            // Estado de carga
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
                    showPostNotification('¡Suscripción exitosa!', 'success');
                    this.reset();
                } else {
                    showPostNotification(data.message || 'Error al suscribir', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showPostNotification('Error de conexión', 'error');
            }
            
            // Restaurar botón
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Scroll spy para tabla de contenidos
function initScrollSpy() {
    const headings = document.querySelectorAll('.post-content h1, .post-content h2, .post-content h3, .post-content h4, .post-content h5, .post-content h6');
    const tocLinks = document.querySelectorAll('#table-of-contents a');
    
    if (headings.length === 0 || tocLinks.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const headingId = entry.target.id;
                const activeLink = document.querySelector(`#table-of-contents a[href="#${headingId}"]`);
                if (activeLink) {
                    updateActiveTocLink(activeLink);
                }
            }
        });
    }, {
        rootMargin: '-20% 0px -70% 0px'
    });
    
    headings.forEach(heading => observer.observe(heading));
}

// Copiar código de bloques de código
function initCopyCodeBlocks() {
    const codeBlocks = document.querySelectorAll('pre code');
    
    codeBlocks.forEach(codeBlock => {
        const pre = codeBlock.parentElement;
        
        // Crear botón de copiar
        const copyButton = document.createElement('button');
        copyButton.innerHTML = '<i class="fas fa-copy"></i>';
        copyButton.className = 'copy-code-btn';
        copyButton.style.cssText = `
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.3s ease;
        `;
        
        copyButton.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(255, 255, 255, 0.3)';
        });
        
        copyButton.addEventListener('mouseleave', function() {
            this.style.background = 'rgba(255, 255, 255, 0.2)';
        });
        
        copyButton.addEventListener('click', function() {
            navigator.clipboard.writeText(codeBlock.textContent).then(() => {
                this.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
                showPostNotification('Código copiado al portapapeles', 'success', 2000);
            });
        });
        
        // Hacer el pre relativo para posicionar el botón
        pre.style.position = 'relative';
        pre.appendChild(copyButton);
    });
}

// Lightbox para imágenes
function initImageLightbox() {
    const images = document.querySelectorAll('.post-content img');
    
    images.forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            openImageLightbox(this.src, this.alt);
        });
    });
}

// Abrir lightbox de imagen
function openImageLightbox(src, alt) {
    const lightbox = document.createElement('div');
    lightbox.className = 'image-lightbox';
    lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = src;
    img.alt = alt;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    `;
    
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 12px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.25rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    lightbox.appendChild(img);
    lightbox.appendChild(closeBtn);
    document.body.appendChild(lightbox);
    
    // Cerrar lightbox
    const closeLightbox = () => {
        document.body.removeChild(lightbox);
    };
    
    lightbox.addEventListener('click', closeLightbox);
    closeBtn.addEventListener('click', closeLightbox);
    
    // Cerrar con ESC
    const handleKeydown = (e) => {
        if (e.key === 'Escape') {
            closeLightbox();
            document.removeEventListener('keydown', handleKeydown);
        }
    };
    document.addEventListener('keydown', handleKeydown);
    
    // Prevenir cierre al hacer clic en la imagen
    img.addEventListener('click', (e) => e.stopPropagation());
}

// Sistema de notificaciones específico para posts
function showPostNotification(message, type = 'info', duration = 4000) {
    const notification = document.createElement('div');
    notification.className = `post-notification post-notification-${type}`;
    
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${colors[type]};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        transform: translateY(100px);
        transition: transform 0.3s ease;
        max-width: 300px;
        font-weight: 500;
        font-size: 0.875rem;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    // Auto-remover
    setTimeout(() => {
        notification.style.transform = 'translateY(100px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Tiempo de lectura estimado
function updateReadingTime() {
    const content = document.querySelector('.post-content');
    if (!content) return;
    
    const text = content.textContent || content.innerText;
    const words = text.trim().split(/\s+/).length;
    const readingTime = Math.ceil(words / 200); // 200 palabras por minuto
    
    const readTimeElements = document.querySelectorAll('.read-time');
    readTimeElements.forEach(element => {
        element.textContent = `${readingTime} min`;
    });
}

// Smooth scroll para enlaces internos
function initSmoothScrolling() {
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

// Inicializar componentes adicionales
document.addEventListener('DOMContentLoaded', function() {
    updateReadingTime();
    initSmoothScrolling();
});

// Exportar funciones para uso global
window.openImageLightbox = openImageLightbox;
window.showPostNotification = showPostNotification;
