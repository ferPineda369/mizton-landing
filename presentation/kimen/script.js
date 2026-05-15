class Presentation {
    constructor() {
        this.currentSlide = 1;
        this.totalSlides = 11;
        this.slides = document.querySelectorAll('.slide');
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.currentSlideEl = document.getElementById('current-slide');
        this.totalSlidesEl = document.getElementById('total-slides');
        this.progressFill = document.getElementById('progress-fill');
        
        this.init();
    }
    
    init() {
        this.totalSlidesEl.textContent = this.totalSlides;
        this.positionSlides();
        this.updateUI();
        this.bindEvents();
        this.bindKeyboard();
        this.bindTouch();
    }
    
    positionSlides() {
        // Position all slides: left (-100%), center (0), right (+100%)
        this.slides.forEach((slide, index) => {
            const slideNum = index + 1;
            if (slideNum === this.currentSlide) {
                slide.style.transform = 'translateX(0)';
                slide.style.opacity = '1';
                slide.classList.add('active');
            } else if (slideNum < this.currentSlide) {
                slide.style.transform = 'translateX(-100%)';
                slide.style.opacity = '0';
                slide.classList.remove('active');
            } else {
                slide.style.transform = 'translateX(100%)';
                slide.style.opacity = '0';
                slide.classList.remove('active');
            }
        });
    }
    
    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.prevSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());
    }
    
    getActiveSlide() {
        return document.querySelector('.slide.active');
    }
    
    canScrollDown() {
        const slide = this.getActiveSlide();
        if (!slide) return false;
        return slide.scrollHeight > slide.clientHeight && 
               slide.scrollTop + slide.clientHeight < slide.scrollHeight - 2;
    }
    
    canScrollUp() {
        const slide = this.getActiveSlide();
        if (!slide) return false;
        return slide.scrollTop > 2;
    }
    
    bindKeyboard() {
        document.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'ArrowRight':
                    e.preventDefault();
                    this.nextSlide();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    this.prevSlide();
                    break;
                case 'ArrowDown':
                case ' ':
                    e.preventDefault();
                    if (this.canScrollDown()) {
                        this.getActiveSlide().scrollBy({ top: 100, behavior: 'smooth' });
                    } else {
                        this.nextSlide();
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (this.canScrollUp()) {
                        this.getActiveSlide().scrollBy({ top: -100, behavior: 'smooth' });
                    } else {
                        this.prevSlide();
                    }
                    break;
                case 'Home':
                    e.preventDefault();
                    this.goToSlide(1);
                    break;
                case 'End':
                    e.preventDefault();
                    this.goToSlide(this.totalSlides);
                    break;
            }
        });
    }
    
    bindTouch() {
        let startX = 0;
        let startY = 0;
        let startScrollTop = 0;
        
        document.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            const active = this.getActiveSlide();
            startScrollTop = active ? active.scrollTop : 0;
        }, { passive: true });
        
        document.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            const diffX = startX - endX;
            const diffY = startY - endY;
            const absX = Math.abs(diffX);
            const absY = Math.abs(diffY);
            
            // Only navigate on clear horizontal swipe
            // AND when not scrolling vertically within slide
            if (absX > absY && absX > 50 && absY < 80) {
                const active = this.getActiveSlide();
                const scrolled = active ? Math.abs(active.scrollTop - startScrollTop) : 0;
                if (scrolled < 10) {
                    if (diffX > 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }
            }
        }, { passive: true });
    }
    
    prevSlide() {
        if (this.currentSlide > 1) {
            this.goToSlide(this.currentSlide - 1);
        }
    }
    
    nextSlide() {
        if (this.currentSlide < this.totalSlides) {
            this.goToSlide(this.currentSlide + 1);
        }
    }
    
    goToSlide(slideNumber) {
        if (slideNumber < 1 || slideNumber > this.totalSlides) return;
        
        this.currentSlide = slideNumber;
        this.positionSlides();
        this.updateUI();
    }
    
    updateUI() {
        this.currentSlideEl.textContent = this.currentSlide;
        this.prevBtn.disabled = this.currentSlide === 1;
        this.nextBtn.disabled = this.currentSlide === this.totalSlides;
        
        const progress = (this.currentSlide / this.totalSlides) * 100;
        this.progressFill.style.width = `${progress}%`;
        
        this.retriggerAnimations();
    }
    
    retriggerAnimations() {
        const activeSlide = document.querySelector('.slide.active');
        if (!activeSlide) return;
        
        const animatedElements = activeSlide.querySelectorAll('.fade-in');
        animatedElements.forEach((el) => {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Presentation();
    initVestingSlider();
});

function initVestingSlider() {
    const slider = document.getElementById('vesting-slider');
    const dayEl = document.getElementById('vesting-day');
    const percentEl = document.getElementById('vesting-percent');
    const tokensEl = document.getElementById('vesting-tokens');
    const fillEl = document.getElementById('vesting-progress-fill');
    
    if (!slider) return;
    
    const TOTAL_TOKENS = 1.0;
    const CLIFF_DAYS = 180;
    const TOTAL_DAYS = 780;
    const CLIFF_PERCENT = 0.10;
    const STEP_PERCENT = 0.045;
    const STEP_DAYS = 30;
    const TOTAL_STEPS = 20; // 20 pasos mensuales tras el cliff (20 x 4.5% = 90%)
    
    function updateVesting(day) {
        let barPercent = 0;
        let tokensUnlocked = 0;
        
        if (day < CLIFF_DAYS) {
            // Durante el cliff: barra vacia, 0 tokens
            barPercent = 0;
            tokensUnlocked = 0;
        } else if (day >= CLIFF_DAYS && day < TOTAL_DAYS) {
            // Despues del cliff: 10% base + 20 pasos mensuales de 4.5%
            const daysAfterCliff = day - CLIFF_DAYS;
            const completedSteps = Math.floor(daysAfterCliff / STEP_DAYS);
            const cappedSteps = Math.min(completedSteps, TOTAL_STEPS);
            barPercent = CLIFF_PERCENT + (cappedSteps * STEP_PERCENT);
            tokensUnlocked = TOTAL_TOKENS * barPercent;
        } else {
            // 780+ dias: 100%
            barPercent = 1.0;
            tokensUnlocked = TOTAL_TOKENS;
        }
        
        dayEl.textContent = day;
        percentEl.textContent = (barPercent * 100).toFixed(1) + '%';
        tokensEl.textContent = tokensUnlocked.toFixed(3);
        fillEl.style.width = (barPercent * 100) + '%';
    }
    
    slider.addEventListener('input', (e) => {
        updateVesting(parseInt(e.target.value));
    });
    
    // Initialize
    updateVesting(0);
}
