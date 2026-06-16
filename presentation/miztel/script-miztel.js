/* ==========================================================================
   MIZTEL PRESENTATION - JAVASCRIPT
   ========================================================================== */

// --------------------------------------------------------------------------
// VARIABLES GLOBALES
// --------------------------------------------------------------------------
let currentSlide = 1;
const totalSlides = 9;
let isTransitioning = false;

// --------------------------------------------------------------------------
// INICIALIZACIÓN
// --------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initializePresentation();
    setupEventListeners();
    updateProgressBar();
    updateSlideCounter();
    initializeGrowthChart();
});

function initializePresentation() {
    // Mostrar primer slide
    showSlide(1);
    
    // Configurar navegación con teclado
    document.addEventListener('keydown', handleKeyPress);
    
    // Configurar swipe para móviles
    setupSwipeGestures();
}

// --------------------------------------------------------------------------
// MANEJO DE SLIDES
// --------------------------------------------------------------------------
function showSlide(slideNumber) {
    if (isTransitioning || slideNumber < 1 || slideNumber > totalSlides) return;
    
    isTransitioning = true;
    
    // Ocultar slide actual
    const currentSlideEl = document.querySelector('.slide.active');
    if (currentSlideEl) {
        currentSlideEl.classList.remove('active');
    }
    
    // Mostrar nuevo slide
    const newSlideEl = document.querySelector(`[data-slide="${slideNumber}"]`);
    if (newSlideEl) {
        newSlideEl.classList.add('active');
        
        // Reiniciar animaciones
        const animatedElements = newSlideEl.querySelectorAll('.fade-in');
        animatedElements.forEach(el => {
            el.style.animation = 'none';
            el.offsetHeight; // Trigger reflow
            el.style.animation = null;
        });
        
        // Inicializar elementos específicos del slide
        initializeSlideElements(slideNumber);
    }
    
    currentSlide = slideNumber;
    updateProgressBar();
    updateSlideCounter();
    
    setTimeout(() => {
        isTransitioning = false;
    }, 300);
}

function initializeSlideElements(slideNumber) {
    switch(slideNumber) {
        case 6:
            // Inicializar gráfico de crecimiento si no está ya creado
            if (!window.growthChartInitialized) {
                setTimeout(() => initializeGrowthChart(), 100);
            }
            break;
        case 7:
            // Asegurar que el simulador esté visible
            const simulatorFrame = document.querySelector('.simulator-frame');
            if (simulatorFrame) {
                simulatorFrame.style.opacity = '0';
                setTimeout(() => {
                    simulatorFrame.style.transition = 'opacity 0.5s ease';
                    simulatorFrame.style.opacity = '1';
                }, 300);
            }
            break;
    }
}

// --------------------------------------------------------------------------
// NAVEGACIÓN
// --------------------------------------------------------------------------
function setupEventListeners() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => navigateSlide(-1));
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => navigateSlide(1));
    }
}

function navigateSlide(direction) {
    const newSlide = currentSlide + direction;
    showSlide(newSlide);
}

function handleKeyPress(e) {
    switch(e.key) {
        case 'ArrowLeft':
            navigateSlide(-1);
            break;
        case 'ArrowRight':
            navigateSlide(1);
            break;
        case 'Home':
            showSlide(1);
            break;
        case 'End':
            showSlide(totalSlides);
            break;
        case 'Escape':
            toggleFullscreen();
            break;
    }
}

function setupSwipeGestures() {
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                navigateSlide(1); // Swipe left - next slide
            } else {
                navigateSlide(-1); // Swipe right - previous slide
            }
        }
    }
}

// --------------------------------------------------------------------------
// UI UPDATES
// --------------------------------------------------------------------------
function updateProgressBar() {
    const progressFill = document.getElementById('progress-fill');
    if (progressFill) {
        const progress = (currentSlide / totalSlides) * 100;
        progressFill.style.width = `${progress}%`;
    }
}

function updateSlideCounter() {
    const currentSlideEl = document.getElementById('current-slide');
    const totalSlidesEl = document.getElementById('total-slides');
    
    if (currentSlideEl) currentSlideEl.textContent = currentSlide;
    if (totalSlidesEl) totalSlidesEl.textContent = totalSlides;
}

// --------------------------------------------------------------------------
// GRÁFICO DE CRECIMIENTO
// --------------------------------------------------------------------------
function initializeGrowthChart() {
    const canvas = document.getElementById('growthChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Configurar canvas para alta densidad de píxeles
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    ctx.scale(dpr, dpr);
    
    // Datos del gráfico
    const data = {
        labels: ['Inicio', 'Año 1', 'Año 2', 'Año 3'],
        values: [0, 500000, 1200000, 2000000]
    };
    
    const chartWidth = rect.width;
    const chartHeight = rect.height;
    const padding = 60;
    const plotWidth = chartWidth - (padding * 2);
    const plotHeight = chartHeight - (padding * 2);
    
    // Limpiar canvas
    ctx.clearRect(0, 0, chartWidth, chartHeight);
    
    // Dibujar fondo
    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
    ctx.fillRect(0, 0, chartWidth, chartHeight);
    
    // Configurar estilos
    ctx.strokeStyle = '#00d4ff';
    ctx.fillStyle = '#00d4ff';
    ctx.lineWidth = 3;
    ctx.font = '14px Arial';
    
    // Encontrar valores máximos
    const maxValue = Math.max(...data.values);
    const xStep = plotWidth / (data.labels.length - 1);
    
    // Dibujar líneas de grid
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
    ctx.lineWidth = 1;
    
    // Grid horizontal
    for (let i = 0; i <= 5; i++) {
        const y = padding + (plotHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(padding + plotWidth, y);
        ctx.stroke();
        
        // Etiquetas de valores
        const value = Math.round(maxValue - (maxValue / 5) * i);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.textAlign = 'right';
        ctx.fillText(formatNumber(value), padding - 10, y + 5);
    }
    
    // Grid vertical
    for (let i = 0; i < data.labels.length; i++) {
        const x = padding + xStep * i;
        ctx.beginPath();
        ctx.moveTo(x, padding);
        ctx.lineTo(x, padding + plotHeight);
        ctx.stroke();
        
        // Etiquetas de años
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.textAlign = 'center';
        ctx.fillText(data.labels[i], x, padding + plotHeight + 25);
    }
    
    // Dibujar línea de crecimiento con gradiente
    const gradient = ctx.createLinearGradient(0, padding, 0, padding + plotHeight);
    gradient.addColorStop(0, 'rgba(0, 212, 255, 0.8)');
    gradient.addColorStop(1, 'rgba(0, 212, 255, 0.1)');
    
    ctx.strokeStyle = '#00d4ff';
    ctx.lineWidth = 3;
    ctx.beginPath();
    
    data.values.forEach((value, index) => {
        const x = padding + xStep * index;
        const y = padding + plotHeight - (value / maxValue) * plotHeight;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
    
    // Rellenar área bajo la curva
    ctx.lineTo(padding + plotWidth, padding + plotHeight);
    ctx.lineTo(padding, padding + plotHeight);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();
    
    // Dibujar puntos de datos
    data.values.forEach((value, index) => {
        const x = padding + xStep * index;
        const y = padding + plotHeight - (value / maxValue) * plotHeight;
        
        // Punto exterior
        ctx.beginPath();
        ctx.arc(x, y, 8, 0, Math.PI * 2);
        ctx.fillStyle = '#00d4ff';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Valor en el punto
        if (value > 0) {
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(formatNumber(value), x, y - 15);
        }
    });
    
    // Título del gráfico
    ctx.fillStyle = '#fff';
    ctx.font = 'bold 18px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Líneas Activas - Proyección 3 Años', chartWidth / 2, 30);
    
    window.growthChartInitialized = true;
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(0) + 'K';
    }
    return num.toString();
}

// --------------------------------------------------------------------------
// FULLSCREEN
// --------------------------------------------------------------------------
function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

// --------------------------------------------------------------------------
// PRESENTATION CONTROLS (opcional - para presentador)
// --------------------------------------------------------------------------
document.addEventListener('keydown', (e) => {
    // Ctrl + P para pausar/reanudar
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        togglePresentationMode();
    }
});

function togglePresentationMode() {
    const isPresentationMode = document.body.classList.toggle('presentation-mode');
    
    if (isPresentationMode) {
        // Ocultar controles de navegación
        document.querySelector('.navigation').style.display = 'none';
        document.querySelector('.progress-bar').style.display = 'none';
        
        // Mostrar indicador de modo presentación
        showPresentationIndicator();
    } else {
        // Mostrar controles
        document.querySelector('.navigation').style.display = 'flex';
        document.querySelector('.progress-bar').style.display = 'block';
        
        // Ocultar indicador
        hidePresentationIndicator();
    }
}

function showPresentationIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'presentation-indicator';
    indicator.innerHTML = '🎯 Modo Presentación';
    indicator.style.cssText = `
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(0, 212, 255, 0.9);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: bold;
        z-index: 1000;
        animation: pulse 2s infinite;
    `;
    document.body.appendChild(indicator);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        hidePresentationIndicator();
    }, 3000);
}

function hidePresentationIndicator() {
    const indicator = document.getElementById('presentation-indicator');
    if (indicator) {
        indicator.remove();
    }
}

// --------------------------------------------------------------------------
// AUTO-PLAY (opcional)
// --------------------------------------------------------------------------
let autoPlayInterval = null;

function toggleAutoPlay() {
    if (autoPlayInterval) {
        stopAutoPlay();
    } else {
        startAutoPlay();
    }
}

function startAutoPlay() {
    autoPlayInterval = setInterval(() => {
        if (currentSlide < totalSlides) {
            navigateSlide(1);
        } else {
            stopAutoPlay();
        }
    }, 5000); // Cambiar slide cada 5 segundos
}

function stopAutoPlay() {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
        autoPlayInterval = null;
    }
}

// --------------------------------------------------------------------------
// IMPRESIÓN Y EXPORTACIÓN
// ----------
function exportToPDF() {
    window.print();
}

// Configurar estilos para impresión
const printStyles = document.createElement('style');
printStyles.textContent = `
    @media print {
        .navigation, .progress-bar {
            display: none !important;
        }
        
        .slide {
            page-break-after: always;
            height: 100vh;
        }
        
        .slide:last-child {
            page-break-after: auto;
        }
    }
`;
document.head.appendChild(printStyles);

// --------------------------------------------------------------------------
// PERFORMANCE OPTIMIZATION
// --------------------------------------------------------------------------
// Lazy loading para gráficos y elementos pesados
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const lazyLoadObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const slideNumber = parseInt(entry.target.dataset.slide);
            if (slideNumber === 6 && !window.growthChartInitialized) {
                initializeGrowthChart();
            }
        }
    });
}, observerOptions);

// Observar todos los slides
document.querySelectorAll('.slide').forEach(slide => {
    lazyLoadObserver.observe(slide);
});

// --------------------------------------------------------------------------
// ERROR HANDLING
// ----------
window.addEventListener('error', (e) => {
    console.error('Error en presentación:', e.error);
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Promesa rechazada:', e.reason);
});

// --------------------------------------------------------------------------
// UTILIDADES
// ----------
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Redimensionar gráficos cuando cambia el tamaño de ventana
const resizeHandler = debounce(() => {
    if (currentSlide === 6) {
        initializeGrowthChart();
    }
}, 250);

window.addEventListener('resize', resizeHandler);
