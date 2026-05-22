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
    
    // Floating Menu
    initFloatingMenu();
    
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
// FLOATING MENU
// ==========================================================================
function initFloatingMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const menuDropdown = document.getElementById('menu-dropdown');
    
    if (!menuToggle || !menuDropdown) return;
    
    // Toggle menu on button click
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        menuToggle.classList.toggle('active');
        menuDropdown.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!menuToggle.contains(e.target) && !menuDropdown.contains(e.target)) {
            menuToggle.classList.remove('active');
            menuDropdown.classList.remove('active');
        }
    });
    
    // Close menu when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menuToggle.classList.remove('active');
            menuDropdown.classList.remove('active');
        }
    });
}

// ==========================================================================
// KIMEN SIMULATION (Slide 18)
// ==========================================================================
let kimenStep = 0;
const kimenMessages = [
    "Presiona \"Avanzar Paso\" para ver la simulación del vesting de KIMEN",
    "Compras 10 tokens KIMEN. El proyecto le asigna esos 10 tokens en el smart contract (bóveda).",
    "Tienes un cliff de 6 meses y un vesting de 20 meses. Durante el cliff no recibes nada.",
    "Durante los primeros 6 meses no recibes nada. El contrato mantiene tus tokens bloqueados.",
    "Al terminar el mes 6, el contrato libera la primera porción (10%) = 1 KIMEN a tu wallet.",
    "Después, cada mes se libera otra parte (4.5%). Mes 7: 1.45 KIMEN, Mes 8: 1.90 KIMEN...",
    "Mes 20: ya recibiste los 10 tokens completos en tu wallet. ¡Vesting completado!",
    "Pool Global: tu compra también genera un bono para tu red. Un pool se activa.",
    "Mes 3: El pool global empieza a distribuirse a tu red de patrocinados.",
    "El pool continúa distribuyéndose mensualmente, incluso después de completar tu vesting (mes 30)."
];

function initKimenSimulation() {
    const monthsContainer = document.getElementById('kimen-months');
    const walletContainer = document.getElementById('kimen-wallet-amounts');
    const poolContainer = document.getElementById('kimen-pool-amounts');
    
    if (!monthsContainer || !walletContainer || !poolContainer) return;
    
    // Generate 26 months
    for (let i = 1; i <= 26; i++) {
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
        walletDiv.textContent = '0 KIMEN';
        walletContainer.appendChild(walletDiv);
        
        // Pool cell
        const poolDiv = document.createElement('div');
        poolDiv.className = 'kimen-pool hidden';
        poolDiv.id = `pool-${i}`;
        poolDiv.innerHTML = '<span class="pool-icon">💰</span>';
        poolContainer.appendChild(poolDiv);
    }
}

function advanceKimenStep() {
    kimenStep++;
    
    const messageEl = document.getElementById('kimen-message');
    const indicatorEl = document.getElementById('kimen-step-indicator');
    const btnEl = document.getElementById('kimen-advance-btn');
    
    if (kimenStep > 9) {
        kimenStep = 9;
        return;
    }
    
    // Update message
    if (messageEl) {
        messageEl.textContent = kimenMessages[kimenStep];
    }
    
    // Update indicator
    if (indicatorEl) {
        indicatorEl.textContent = `Paso ${kimenStep + 1} de 9`;
    }
    
    // Disable button if last step
    if (kimenStep === 9 && btnEl) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<span>✓</span> Completado';
    }
    
    // Execute step actions
    switch(kimenStep) {
        case 1: // Show vault with 10 tokens
            highlightMonths(1, 26, 'disabled');
            break;
            
        case 2: // Cliff period (months 1-6)
            highlightMonths(1, 6, 'cliff');
            break;
            
        case 3: // Reinforce cliff
            highlightMonths(1, 6, 'cliff');
            break;
            
        case 4: // Month 6 - First release (10%)
            document.getElementById('month-6').className = 'kimen-month complete';
            document.getElementById('wallet-6').className = 'kimen-amount partial';
            document.getElementById('wallet-6').textContent = '1.00 KIMEN';
            break;
            
        case 5: // Vesting progression months 7-20
            highlightMonths(7, 20, 'active');
            // Calculate and show progressive amounts
            for (let i = 7; i <= 20; i++) {
                const amount = 1 + ((i - 6) * 0.45); // 10% + 4.5% per month
                const displayAmount = Math.min(amount, 10).toFixed(2);
                const walletEl = document.getElementById(`wallet-${i}`);
                if (walletEl) {
                    walletEl.className = 'kimen-amount partial';
                    walletEl.textContent = `${displayAmount} KIMEN`;
                }
            }
            break;
            
        case 6: // Complete at month 20
            for (let i = 1; i <= 20; i++) {
                const monthEl = document.getElementById(`month-${i}`);
                if (monthEl) monthEl.className = 'kimen-month complete';
            }
            document.getElementById('wallet-20').className = 'kimen-amount complete';
            document.getElementById('wallet-20').textContent = '10.00 KIMEN';
            break;
            
        case 7: // Pool Global appears
            for (let i = 1; i <= 26; i++) {
                const poolEl = document.getElementById(`pool-${i}`);
                if (poolEl) {
                    poolEl.className = 'kimen-pool visible';
                    poolEl.innerHTML = '<span class="pool-icon">💰</span> Pool';
                }
            }
            break;
            
        case 8: // Pool distributes at month 3
            const pool3 = document.getElementById('pool-3');
            if (pool3) {
                pool3.innerHTML = '<span class="pool-icon network">👥</span> Red';
                pool3.style.background = 'rgba(0, 230, 118, 0.2)';
                pool3.style.borderColor = '#00e676';
            }
            break;
            
        case 9: // Pool continues through month 30
            for (let i = 1; i <= 26; i++) {
                const poolEl = document.getElementById(`pool-${i}`);
                if (poolEl) {
                    poolEl.innerHTML = '<span class="pool-icon network">👥</span> Red';
                    poolEl.style.background = 'rgba(0, 230, 118, 0.15)';
                    poolEl.style.borderColor = '#00e676';
                }
            }
            break;
    }
}

function highlightMonths(start, end, className) {
    for (let i = start; i <= end; i++) {
        const monthEl = document.getElementById(`month-${i}`);
        if (monthEl) {
            monthEl.className = `kimen-month ${className}`;
        }
    }
}
