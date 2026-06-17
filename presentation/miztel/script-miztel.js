/* ==========================================================================
   MIZTEL PRESENTATION - JAVASCRIPT
   ========================================================================== */

// --------------------------------------------------------------------------
// VARIABLES GLOBALES
// --------------------------------------------------------------------------
let currentSlide = 1;
const totalSlides = 10;
let isTransitioning = false;

// --------------------------------------------------------------------------
// INICIALIZACIÓN
// --------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initializePresentation();
    setupEventListeners();
    updateProgressBar();
    updateSlideCounter();
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
            // Inicializar gráfico de dona interactivo
            if (!window.donutChartInitialized) {
                setTimeout(() => initializeDonutChart(), 100);
            }
            break;
        case 8:
            // Inicializar gráfico de crecimiento (Los 12 Niveles)
            if (!window.growthChartInitialized) {
                setTimeout(() => initializeGrowthChart(), 100);
            }
            break;
    }
}

// --------------------------------------------------------------------------
// GRÁFICO DE DONA INTERACTIVO
// --------------------------------------------------------------------------
function initializeDonutChart() {
    const canvas = document.getElementById('donutChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const size = 300;
    canvas.width = size * dpr;
    canvas.height = size * dpr;
    canvas.style.width = size + 'px';
    canvas.style.height = size + 'px';
    ctx.scale(dpr, dpr);

    const cx = size / 2;
    const cy = size / 2;
    const outerR = 120;
    const innerR = 68;

    const segments = [
        { label: 'Red de Recomendación', pct: 65, color: '#00d9ff' },
        { label: 'Poseedores del Token',  pct: 20, color: '#7b2ff7' },
        { label: 'Fondo Global (Mizton)', pct:  5, color: '#e85d04' },
        { label: 'Operación Técnica',     pct:  5, color: '#00c896' },
        { label: 'Tesorería de Reserva',  pct:  5, color: '#ff0080' }
    ];

    // Pre-compute arc angles
    let startAngle = -Math.PI / 2;
    const arcs = segments.map(seg => {
        const angle = (seg.pct / 100) * 2 * Math.PI;
        const arc = { ...seg, startAngle, endAngle: startAngle + angle };
        startAngle += angle;
        return arc;
    });

    let hoveredIndex = -1;
    const tooltip = document.getElementById('donut-tooltip');

    function drawChart(hovered) {
        ctx.clearRect(0, 0, size, size);

        arcs.forEach((arc, i) => {
            const isHovered = i === hovered;
            const expandBy = isHovered ? 10 : 0;
            const midAngle = (arc.startAngle + arc.endAngle) / 2;
            const ox = isHovered ? Math.cos(midAngle) * expandBy : 0;
            const oy = isHovered ? Math.sin(midAngle) * expandBy : 0;

            // Glow shadow
            ctx.save();
            ctx.shadowColor = arc.color;
            ctx.shadowBlur = isHovered ? 28 : 10;

            // Segment
            ctx.beginPath();
            ctx.moveTo(cx + ox, cy + oy);
            ctx.arc(cx + ox, cy + oy, outerR, arc.startAngle, arc.endAngle);
            ctx.arc(cx + ox, cy + oy, innerR, arc.endAngle, arc.startAngle, true);
            ctx.closePath();
            ctx.fillStyle = arc.color;
            ctx.globalAlpha = isHovered ? 1 : 0.82;
            ctx.fill();

            // Border between segments
            ctx.strokeStyle = 'rgba(15, 52, 96, 0.9)';
            ctx.lineWidth = isHovered ? 3 : 2;
            ctx.stroke();
            ctx.restore();

            // Percentage label
            const labelR = (outerR + innerR) / 2;
            const lx = cx + Math.cos(midAngle) * labelR + ox;
            const ly = cy + Math.sin(midAngle) * labelR + oy;
            ctx.save();
            ctx.fillStyle = '#ffffff';
            ctx.font = `bold ${isHovered ? 17 : 14}px "Space Grotesk", sans-serif`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.shadowColor = 'rgba(0,0,0,0.7)';
            ctx.shadowBlur = 4;
            ctx.fillText(arc.pct + '%', lx, ly);
            ctx.restore();
        });

        // Center text
        ctx.save();
        ctx.fillStyle = hovered >= 0 ? arcs[hovered].color : '#00d9ff';
        ctx.font = `bold 22px "Space Grotesk", sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = hovered >= 0 ? arcs[hovered].color : '#00d9ff';
        ctx.shadowBlur = 15;
        ctx.fillText(hovered >= 0 ? arcs[hovered].pct + '%' : '100%', cx, cy - 10);
        ctx.font = `13px "Space Grotesk", sans-serif`;
        ctx.shadowBlur = 0;
        ctx.fillStyle = 'rgba(255,255,255,0.55)';
        ctx.fillText(hovered >= 0 ? 'del total' : 'distribuido', cx, cy + 14);
        ctx.restore();
    }

    function getSegmentAt(x, y) {
        const dx = x - cx, dy = y - cy;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < innerR || dist > outerR + 12) return -1;
        let angle = Math.atan2(dy, dx);
        if (angle < -Math.PI / 2) angle += 2 * Math.PI;
        for (let i = 0; i < arcs.length; i++) {
            if (angle >= arcs[i].startAngle && angle <= arcs[i].endAngle) return i;
        }
        return -1;
    }

    function highlightLegend(index) {
        document.querySelectorAll('.legend-item[data-segment]').forEach(el => {
            el.classList.toggle('active', parseInt(el.dataset.segment) === index);
        });
    }

    canvas.addEventListener('mousemove', e => {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const idx = getSegmentAt(x, y);

        if (idx !== hoveredIndex) {
            hoveredIndex = idx;
            drawChart(hoveredIndex);
            highlightLegend(hoveredIndex);
        }

        if (idx >= 0) {
            const seg = arcs[idx];
            tooltip.textContent = seg.pct + '% — ' + seg.label;
            tooltip.style.borderColor = seg.color;
            tooltip.style.left = (e.clientX - canvas.getBoundingClientRect().left + 15) + 'px';
            tooltip.style.top  = (e.clientY - canvas.getBoundingClientRect().top  - 10) + 'px';
            tooltip.classList.add('visible');
        } else {
            tooltip.classList.remove('visible');
        }
    });

    canvas.addEventListener('mouseleave', () => {
        hoveredIndex = -1;
        drawChart(-1);
        highlightLegend(-1);
        tooltip.classList.remove('visible');
    });

    // Legend hover → highlight on chart
    document.querySelectorAll('.legend-item[data-segment]').forEach(el => {
        el.addEventListener('mouseenter', () => {
            const i = parseInt(el.dataset.segment);
            hoveredIndex = i;
            drawChart(i);
            highlightLegend(i);
        });
        el.addEventListener('mouseleave', () => {
            hoveredIndex = -1;
            drawChart(-1);
            highlightLegend(-1);
        });
    });

    drawChart(-1);
    window.donutChartInitialized = true;
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
    
    // Datos del gráfico - Los 12 Niveles de la Comunidad
    const data = {
        labels: ['Paso 1', 'Paso 4', 'Paso 8', 'Paso 12'],
        values: [512, 4096, 65536, 2097150]
    };
    
    const chartWidth = rect.width;
    const chartHeight = rect.height;
    const padding = 60;
    const plotWidth = chartWidth - (padding * 2);
    const plotHeight = chartHeight - (padding * 2);
    
    // Limpiar canvas
    ctx.clearRect(0, 0, chartWidth, chartHeight);
    
    // Dibujar fondo con gradiente
    const bgGradient = ctx.createLinearGradient(0, 0, chartWidth, chartHeight);
    bgGradient.addColorStop(0, 'rgba(26, 26, 46, 0.9)');
    bgGradient.addColorStop(1, 'rgba(15, 52, 96, 0.9)');
    ctx.fillStyle = bgGradient;
    ctx.fillRect(0, 0, chartWidth, chartHeight);
    
    // Configurar estilos
    ctx.strokeStyle = '#00d9ff';
    ctx.fillStyle = '#00d9ff';
    ctx.lineWidth = 3;
    ctx.font = '14px "Space Grotesk", sans-serif';
    
    // Encontrar valores máximos
    const maxValue = Math.max(...data.values);
    const xStep = plotWidth / (data.labels.length - 1);
    
    // Dibujar líneas de grid mejoradas
    ctx.strokeStyle = 'rgba(0, 217, 255, 0.1)';
    ctx.lineWidth = 1;
    
    // Grid horizontal con efecto neon
    for (let i = 0; i <= 5; i++) {
        const y = padding + (plotHeight / 5) * i;
        
        // Línea de grid con glow
        ctx.shadowColor = 'rgba(0, 217, 255, 0.3)';
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(padding + plotWidth, y);
        ctx.stroke();
        ctx.shadowBlur = 0;
        
        // Etiquetas de valores con efecto neon
        const value = Math.round(maxValue - (maxValue / 5) * i);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
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
        ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
        ctx.textAlign = 'center';
        ctx.fillText(data.labels[i], x, padding + plotHeight + 25);
    }
    
    // Dibujar línea de crecimiento con gradiente mejorado
    const gradient = ctx.createLinearGradient(0, padding, 0, padding + plotHeight);
    gradient.addColorStop(0, 'rgba(0, 217, 255, 0.9)');
    gradient.addColorStop(0.5, 'rgba(255, 0, 128, 0.6)');
    gradient.addColorStop(1, 'rgba(0, 217, 255, 0.1)');
    
    // Línea principal con efecto glow
    ctx.shadowColor = 'rgba(0, 217, 255, 0.8)';
    ctx.shadowBlur = 20;
    ctx.strokeStyle = '#00d9ff';
    ctx.lineWidth = 4;
    ctx.beginPath();
    
    // Crear curva suave en lugar de línea recta
    data.values.forEach((value, index) => {
        const x = padding + xStep * index;
        const y = padding + plotHeight - (value / maxValue) * plotHeight;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else if (index === 1) {
            // Primer segmento curvo
            const prevX = padding + xStep * (index - 1);
            const prevY = padding + plotHeight - (data.values[index - 1] / maxValue) * plotHeight;
            const cpX = (prevX + x) / 2;
            const cpY = (prevY + y) / 2 - 20;
            ctx.quadraticCurveTo(cpX, cpY, x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
    ctx.shadowBlur = 0;
    
    // Rellenar área bajo la curva con gradiente
    ctx.lineTo(padding + plotWidth, padding + plotHeight);
    ctx.lineTo(padding, padding + plotHeight);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();
    
    // Dibujar puntos de datos con efecto neon
    data.values.forEach((value, index) => {
        const x = padding + xStep * index;
        const y = padding + plotHeight - (value / maxValue) * plotHeight;
        
        if (value > 0) {
            // Glow exterior
            ctx.shadowColor = 'rgba(0, 217, 255, 0.8)';
            ctx.shadowBlur = 20;
            ctx.beginPath();
            ctx.arc(x, y, 12, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(0, 217, 255, 0.3)';
            ctx.fill();
            
            // Punto principal
            ctx.shadowBlur = 0;
            ctx.beginPath();
            ctx.arc(x, y, 8, 0, Math.PI * 2);
            const pointGradient = ctx.createRadialGradient(x, y, 0, x, y, 8);
            pointGradient.addColorStop(0, '#ffffff');
            pointGradient.addColorStop(0.7, '#00d9ff');
            pointGradient.addColorStop(1, '#0099cc');
            ctx.fillStyle = pointGradient;
            ctx.fill();
            
            // Borde del punto
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();
            
            // Valor con efecto neon
            ctx.shadowColor = 'rgba(255, 255, 255, 0.8)';
            ctx.shadowBlur = 10;
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 14px "Space Grotesk", sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(formatNumber(value), x, y - 20);
            ctx.shadowBlur = 0;
        }
    });
    
    // Título del gráfico con efecto neon
    ctx.shadowColor = 'rgba(0, 217, 255, 0.8)';
    ctx.shadowBlur = 20;
    ctx.fillStyle = '#00d9ff';
    ctx.font = 'bold 24px "Space Grotesk", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Usuarios Conectados - 12 Pasos de Crecimiento', chartWidth / 2, 40);
    
    // Subtítulo
    ctx.shadowBlur = 0;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
    ctx.font = '14px "Space Grotesk", sans-serif';
    ctx.fillText('La red se duplica de 2 en 2 hasta 2,097,150 usuarios', chartWidth / 2, 65);
    
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
