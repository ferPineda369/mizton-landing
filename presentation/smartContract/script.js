// Smart Contracts Presentation - Navigation Script

let currentSlide = 1;
const totalSlides = 18;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateSlideCounter();
    updateProgressBar();
    updateNavButtons();
    
    // Event listeners
    document.getElementById('prev-btn').addEventListener('click', prevSlide);
    document.getElementById('next-btn').addEventListener('click', nextSlide);
    
    // Keyboard navigation
    document.addEventListener('keydown', handleKeyboard);
    
    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextSlide(); // Swipe left - next
            } else {
                prevSlide(); // Swipe right - previous
            }
        }
    }
    
    // Initialize KIMEN simulation if on slide 18
    initKimenSimulation();
});

function goToSlide(slideNumber) {
    if (slideNumber < 1 || slideNumber > totalSlides) return;
    
    // Hide current slide
    const currentSlideEl = document.querySelector(`.slide[data-slide="${currentSlide}"]`);
    if (currentSlideEl) {
        currentSlideEl.classList.remove('active');
    }
    
    // Show new slide
    currentSlide = slideNumber;
    const newSlideEl = document.querySelector(`.slide[data-slide="${currentSlide}"]`);
    if (newSlideEl) {
        newSlideEl.classList.add('active');
    }
    
    updateSlideCounter();
    updateProgressBar();
    updateNavButtons();
}

function nextSlide() {
    if (currentSlide < totalSlides) {
        goToSlide(currentSlide + 1);
    }
}

function prevSlide() {
    if (currentSlide > 1) {
        goToSlide(currentSlide - 1);
    }
}

function updateSlideCounter() {
    document.getElementById('current-slide').textContent = currentSlide;
}

function updateProgressBar() {
    const progress = (currentSlide / totalSlides) * 100;
    document.getElementById('progress-fill').style.width = progress + '%';
}

function updateNavButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    prevBtn.disabled = currentSlide === 1;
    nextBtn.disabled = currentSlide === totalSlides;
}

function handleKeyboard(e) {
    switch(e.key) {
        case 'ArrowRight':
        case 'ArrowDown':
        case ' ':
            e.preventDefault();
            nextSlide();
            break;
        case 'ArrowLeft':
        case 'ArrowUp':
            e.preventDefault();
            prevSlide();
            break;
        case 'Home':
            e.preventDefault();
            goToSlide(1);
            break;
        case 'End':
            e.preventDefault();
            goToSlide(totalSlides);
            break;
    }
}

// ==========================================================================
// KIMEN SIMULATION (Slide 18)
// ==========================================================================
let kimenStep = 0;
const TOTAL_KIMEN_STEPS = 13;

const kimenMessages = [
    'Presiona "Avanzar" para ver la simulación del vesting de KIMEN.', // Paso 0
    'Compras 10 tokens KIMEN. El proyecto le asigna esos 10 tokens en el smart contract (bóveda).', // Paso 1
    'Tienes un cliff de 6 meses y un vesting de 20 meses. Durante el cliff no recibes nada.', // Paso 2
    'Durante los primeros 6 meses no recibes nada. El contrato mantiene tus tokens bloqueados.', // Paso 3
    'Al terminar el mes 6, el contrato libera la primera porción (10%) = 1 KIMEN a tu wallet.', // Paso 4
    'Después, cada mes se libera otra parte (4.5%). Mes 7: 1.45 KIMEN, Mes 8: 1.90 KIMEN... hasta que al mes 26...', // Paso 5
    'ya recibiste los 10 tokens completos en tu wallet.', // Paso 6
    'Pool Global: Adicionalmente a partir del 3er mes en adelante, empiezas a obtener ganancias del Pool Global', // Paso 7
    'Cada mes seguirá repartiendo al Pool Global por el total de MIZTON que tengas', // Paso 8
    'Sin importar que sigan bloqueados tus KIMEN o se estén liberando en el Vesting', // Paso 9
    'Esto es gracias al token MIZTON', // Paso 10
    'Entre más token MIZTON poseas, mayor será tu participación en KIMEN y en todos los demás proyectos', // Paso 11
    'Además participas desde el primer reparto particular por poseer el token KIMEN', // Paso 12
    'Siempre que tengas alguno de estos tokens, participarás en el reparto de ganancias correspondiente' // Paso 13
];

// Estado actual de cada paso (para poder retroceder)
let kimenStateHistory = [];

function initKimenSimulation() {
    const monthsContainer = document.getElementById('kimen-months');
    const walletContainer = document.getElementById('kimen-wallet-amounts');
    const poolContainer = document.getElementById('kimen-pool-amounts');
    const gananciasContainer = document.getElementById('kimen-ganancias-amounts');
    
    if (!monthsContainer || !walletContainer || !poolContainer || !gananciasContainer) return;
    
    // Limpiar contenedores
    monthsContainer.innerHTML = '';
    walletContainer.innerHTML = '';
    poolContainer.innerHTML = '';
    gananciasContainer.innerHTML = '';
    
    // Generate 30 months
    for (let i = 1; i <= 30; i++) {
        // Month cell
        const monthDiv = document.createElement('div');
        monthDiv.className = 'kimen-month disabled';
        monthDiv.id = `month-${i}`;
        monthDiv.textContent = `Mes ${i}`;
        monthsContainer.appendChild(monthDiv);
        
        // Wallet amount cell
        const walletDiv = document.createElement('div');
        walletDiv.className = 'kimen-amount zero';
        walletDiv.id = `wallet-${i}`;
        walletDiv.textContent = '-';
        walletContainer.appendChild(walletDiv);
        
        // Pool cell
        const poolDiv = document.createElement('div');
        poolDiv.className = 'kimen-pool hidden';
        poolDiv.id = `pool-${i}`;
        poolDiv.innerHTML = '<span class="pool-icon">-</span>';
        poolContainer.appendChild(poolDiv);
        
        // Ganancias KIMEN cell
        const gananciaDiv = document.createElement('div');
        gananciaDiv.className = 'kimen-ganancia hidden';
        gananciaDiv.id = `ganancia-${i}`;
        gananciaDiv.innerHTML = '<span class="pool-icon">-</span>';
        gananciasContainer.appendChild(gananciaDiv);
    }
    
    // Inicializar en paso 0
    kimenStep = 0;
    kimenStateHistory = [];
    updateKimenUI();
}

function updateKimenUI() {
    const messageEl = document.getElementById('kimen-message');
    const indicatorEl = document.getElementById('kimen-step-indicator');
    const backBtn = document.getElementById('kimen-back-btn');
    const advanceBtn = document.getElementById('kimen-advance-btn');
    
    // Update message con efecto de iluminación
    if (messageEl) {
        messageEl.textContent = kimenMessages[kimenStep];
        // Agregar clase highlight para animación
        messageEl.classList.remove('highlight');
        void messageEl.offsetWidth; // Forzar reflow para reiniciar animación
        messageEl.classList.add('highlight');
        // Remover clase después de la animación
        setTimeout(() => {
            messageEl.classList.remove('highlight');
        }, 800);
    }
    
    // Update indicator
    if (indicatorEl) {
        indicatorEl.textContent = `< Paso ${kimenStep} de ${TOTAL_KIMEN_STEPS} >`;
    }
    
    // Update buttons
    if (backBtn) {
        backBtn.disabled = kimenStep === 0;
    }
    if (advanceBtn) {
        advanceBtn.disabled = kimenStep === TOTAL_KIMEN_STEPS;
    }
}

function saveKimenState() {
    // Guardar el estado actual antes de avanzar
    const state = {
        months: [],
        wallets: [],
        pools: [],
        ganancias: []
    };
    
    for (let i = 1; i <= 30; i++) {
        const monthEl = document.getElementById(`month-${i}`);
        const walletEl = document.getElementById(`wallet-${i}`);
        const poolEl = document.getElementById(`pool-${i}`);
        const gananciaEl = document.getElementById(`ganancia-${i}`);
        
        if (monthEl) state.months.push({ id: i, className: monthEl.className });
        if (walletEl) state.wallets.push({ id: i, className: walletEl.className, text: walletEl.textContent });
        if (poolEl) state.pools.push({ id: i, className: poolEl.className, html: poolEl.innerHTML, bg: poolEl.style.background, border: poolEl.style.borderColor });
        if (gananciaEl) state.ganancias.push({ id: i, className: gananciaEl.className, html: gananciaEl.innerHTML, bg: gananciaEl.style.background, border: gananciaEl.style.borderColor });
    }
    
    kimenStateHistory.push(state);
}

function restoreKimenState(stepIndex) {
    if (stepIndex < 0 || stepIndex >= kimenStateHistory.length) return;
    
    const state = kimenStateHistory[stepIndex];
    
    // Restaurar meses
    state.months.forEach(m => {
        const el = document.getElementById(`month-${m.id}`);
        if (el) el.className = m.className;
    });
    
    // Restaurar wallets
    state.wallets.forEach(w => {
        const el = document.getElementById(`wallet-${w.id}`);
        if (el) {
            el.className = w.className;
            el.textContent = w.text;
        }
    });
    
    // Restaurar pools
    state.pools.forEach(p => {
        const el = document.getElementById(`pool-${p.id}`);
        if (el) {
            el.className = p.className;
            el.innerHTML = p.html;
            if (p.bg) el.style.background = p.bg;
            if (p.border) el.style.borderColor = p.border;
        }
    });
    
    // Restaurar ganancias
    state.ganancias.forEach(g => {
        const el = document.getElementById(`ganancia-${g.id}`);
        if (el) {
            el.className = g.className;
            el.innerHTML = g.html;
            if (g.bg) el.style.background = g.bg;
            if (g.border) el.style.borderColor = g.border;
        }
    });
}

function backKimenStep() {
    if (kimenStep <= 0) return;
    
    // Retroceder un paso
    kimenStep--;
    
    // Restaurar estado del paso anterior
    if (kimenStateHistory.length > 0) {
        kimenStateHistory.pop(); // Eliminar estado actual
        if (kimenStateHistory.length > 0) {
            restoreKimenState(kimenStateHistory.length - 1);
        } else {
            // Si no hay historial, reiniciar
            resetKimenSimulation();
        }
    }
    
    updateKimenUI();
}

function resetKimenSimulation() {
    // Resetear todos los elementos al estado inicial
    for (let i = 1; i <= 30; i++) {
        const monthEl = document.getElementById(`month-${i}`);
        const walletEl = document.getElementById(`wallet-${i}`);
        const poolEl = document.getElementById(`pool-${i}`);
        const gananciaEl = document.getElementById(`ganancia-${i}`);
        
        if (monthEl) {
            monthEl.className = 'kimen-month disabled';
        }
        if (walletEl) {
            walletEl.className = 'kimen-amount zero';
            walletEl.textContent = '-';
        }
        if (poolEl) {
            poolEl.className = 'kimen-pool hidden';
            poolEl.innerHTML = '<span class="pool-icon">-</span>';
            poolEl.style.background = '';
            poolEl.style.borderColor = '';
        }
        if (gananciaEl) {
            gananciaEl.className = 'kimen-ganancia hidden';
            gananciaEl.innerHTML = '<span class="pool-icon">-</span>';
            gananciaEl.style.background = '';
            gananciaEl.style.borderColor = '';
        }
    }
}

function advanceKimenStep() {
    if (kimenStep >= TOTAL_KIMEN_STEPS) return;
    
    // Guardar estado actual antes de avanzar
    saveKimenState();
    
    kimenStep++;
    
    // Execute step actions
    switch(kimenStep) {
        case 1: // Paso 1: Compras 10 tokens KIMEN
            // Mostrar mensaje de reservados en vault
            const vaultTokens = document.getElementById('vault-tokens');
            if (vaultTokens) vaultTokens.textContent = '10 KIMEN reservados para ti';
            break;
            
        case 2: // Paso 2: Cliff de 6 meses - Meses 1-6 amarillos
            for (let i = 1; i <= 6; i++) {
                const monthEl = document.getElementById(`month-${i}`);
                if (monthEl) monthEl.className = 'kimen-month cliff';
            }
            break;
            
        case 3: // Paso 3: Reforzar cliff (sin cambios visuales)
            // Los meses 1-6 ya estÃ¡n en amarillo
            break;
            
        case 4: // Paso 4: Mes 6 libera 10% = 1 KIMEN
            const month6 = document.getElementById('month-6');
            const wallet6 = document.getElementById('wallet-6');
            if (month6) month6.className = 'kimen-month complete';
            if (wallet6) {
                wallet6.className = 'kimen-amount partial';
                wallet6.textContent = '1.00 KIMEN';
            }
            break;
            
        case 5: // Paso 5: Vesting meses 7-25 (sin incluir 26)
            for (let i = 7; i <= 25; i++) {
                const monthEl = document.getElementById(`month-${i}`);
                const walletEl = document.getElementById(`wallet-${i}`);
                
                // Delay progresivo de 80ms por mes
                setTimeout(() => {
                    if (monthEl) monthEl.className = 'kimen-month active';
                    
                    // CÃ¡lculo: 1 + (i-6) * 0.45 = progresiÃ³n
                    const amount = 1 + ((i - 6) * 0.45);
                    const displayAmount = amount.toFixed(2);
                    
                    if (walletEl) {
                        walletEl.className = 'kimen-amount partial';
                        walletEl.textContent = `${displayAmount} KIMEN`;
                    }
                }, (i - 7) * 80);
            }
            break;
            
        case 6: // Paso 6: Mes 26 completo con 10 KIMEN
            const month26 = document.getElementById('month-26');
            const wallet26 = document.getElementById('wallet-26');
            
            if (month26) month26.className = 'kimen-month complete';
            if (wallet26) {
                wallet26.className = 'kimen-amount complete';
                wallet26.textContent = '10.00 KIMEN';
            }
            
            // TambiÃ©n completar meses anteriores con delay
            for (let i = 1; i <= 26; i++) {
                setTimeout(() => {
                    const monthEl = document.getElementById(`month-${i}`);
                    if (monthEl && !monthEl.classList.contains('complete')) {
                        monthEl.className = 'kimen-month complete';
                    }
                }, i * 30);
            }
            break;
            
        case 7: // Paso 7: Pool Global - solo Mes 3
            const pool3 = document.getElementById('pool-3');
            if (pool3) {
                pool3.className = 'kimen-pool visible pool-animate';
                pool3.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
            }
            break;
            
        case 8: // Paso 8: Pool Global - Mes 4
            const pool4 = document.getElementById('pool-4');
            if (pool4) {
                pool4.className = 'kimen-pool visible pool-animate';
                pool4.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
            }
            break;
            
        case 9: // Paso 9: Pool Global - Mes 5
            const pool5 = document.getElementById('pool-5');
            if (pool5) {
                pool5.className = 'kimen-pool visible pool-animate';
                pool5.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
            }
            break;
            
        case 10: // Paso 10: Pool Global - Mes 6
            const pool6 = document.getElementById('pool-6');
            if (pool6) {
                pool6.className = 'kimen-pool visible pool-animate';
                pool6.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
            }
            break;
            
        case 11: // Paso 11: Pool Global - Mes 7
            const pool7 = document.getElementById('pool-7');
            if (pool7) {
                pool7.className = 'kimen-pool visible pool-animate';
                pool7.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
            }
            break;
            
        case 12: // Paso 12: Ganancias KIMEN - Meses 3-7
            for (let i = 3; i <= 7; i++) {
                setTimeout(() => {
                    const gananciaEl = document.getElementById(`ganancia-${i}`);
                    const poolEl = document.getElementById(`pool-${i}`);
                    
                    if (gananciaEl) {
                        gananciaEl.className = 'kimen-ganancia visible pool-animate';
                        gananciaEl.innerHTML = '<span class="pool-icon">&#128176;</span> KIMEN';
                    }
                    
                    // Cambiar Pool a color verde (participacion activa)
                    if (poolEl) {
                        poolEl.style.background = 'rgba(0, 230, 118, 0.15)';
                        poolEl.style.borderColor = '#00e676';
                    }
                }, (i - 3) * 150);
            }
            break;
            
        case 13: // Paso 13: Pool Global y Ganancias KIMEN - Meses 8-30 secuencial
            for (let i = 8; i <= 30; i++) {
                setTimeout(() => {
                    const poolEl = document.getElementById(`pool-${i}`);
                    const gananciaEl = document.getElementById(`ganancia-${i}`);
                    
                    if (poolEl) {
                        poolEl.className = 'kimen-pool visible pool-animate';
                        poolEl.innerHTML = '<span class="pool-icon">&#128176;</span> Pool';
                        poolEl.style.background = 'rgba(0, 230, 118, 0.15)';
                        poolEl.style.borderColor = '#00e676';
                    }
                    
                    if (gananciaEl) {
                        gananciaEl.className = 'kimen-ganancia visible pool-animate';
                        gananciaEl.innerHTML = '<span class="pool-icon">&#128176;</span> KIMEN';
                    }
                }, (i - 8) * 60);
            }
            break;
    }
    
    updateKimenUI();
}

function highlightMonths(start, end, className) {
    for (let i = start; i <= end; i++) {
        const monthEl = document.getElementById(`month-${i}`);
        if (monthEl) {
            monthEl.className = `kimen-month ${className}`;
        }
    }
}

