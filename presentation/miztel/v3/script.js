/* ==========================================================================
   MIZTEL PRESENTATION v3 - JAVASCRIPT
   Motor de slides + fallback de imágenes con descripción sugerida
   ========================================================================== */

class Presentation {
    constructor() {
        this.currentSlide = 1;
        this.totalSlides = 26;
        this.slides = document.querySelectorAll('.slide');
        this.currentSlideEl = document.getElementById('current-slide');
        this.totalSlidesEl  = document.getElementById('total-slides');
        this.progressFill   = document.getElementById('progress-fill');

        this.init();
    }

    init() {
        if (this.totalSlidesEl) this.totalSlidesEl.textContent = this.totalSlides;
        this.setupImagePlaceholders();
        this.positionSlides();
        this.updateUI();
        this.bindKeyboard();
        this.bindTouch();
        this.bindWheel();
    }

    /* Si una imagen no existe, la reemplaza por un placeholder
       con la descripción del tipo de imagen sugerida. */
    setupImagePlaceholders() {
        document.querySelectorAll('img.ph-img').forEach(img => {
            img.addEventListener('error', () => {
                const ph = document.createElement('div');
                ph.className = 'img-ph' + (img.classList.contains('token-img') ? ' token-img' : '');
                ph.innerHTML = `
                    <div class="ph-icon">🖼️</div>
                    <div class="ph-label">IMAGEN SUGERIDA · ${img.getAttribute('src')}</div>
                    <div class="ph-desc">${img.dataset.desc || img.alt}</div>
                `;
                img.replaceWith(ph);
            }, { once: true });
            // Si ya falló antes de registrar el listener
            if (img.complete && img.naturalWidth === 0) {
                img.dispatchEvent(new Event('error'));
            }
        });
    }

    positionSlides() {
        this.slides.forEach((slide, index) => {
            const num = index + 1;
            if (num === this.currentSlide) {
                slide.style.transform = 'translateX(0)';
                slide.style.opacity   = '1';
                slide.classList.add('active');
            } else if (num < this.currentSlide) {
                slide.style.transform = 'translateX(-100%)';
                slide.style.opacity   = '0';
                slide.classList.remove('active');
            } else {
                slide.style.transform = 'translateX(100%)';
                slide.style.opacity   = '0';
                slide.classList.remove('active');
            }
        });
    }

    getActiveSlide() {
        return document.querySelector('.slide.active');
    }

    canScrollDown() {
        const s = this.getActiveSlide();
        if (!s) return false;
        return s.scrollHeight > s.clientHeight && s.scrollTop + s.clientHeight < s.scrollHeight - 2;
    }

    canScrollUp() {
        const s = this.getActiveSlide();
        if (!s) return false;
        return s.scrollTop > 2;
    }

    bindKeyboard() {
        document.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'ArrowRight':
                    e.preventDefault(); this.nextSlide(); break;
                case 'ArrowLeft':
                    e.preventDefault(); this.prevSlide(); break;
                case 'ArrowDown':
                case ' ':
                    e.preventDefault();
                    if (this.canScrollDown()) {
                        this.getActiveSlide().scrollBy({ top: 120, behavior: 'smooth' });
                    } else {
                        this.nextSlide();
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (this.canScrollUp()) {
                        this.getActiveSlide().scrollBy({ top: -120, behavior: 'smooth' });
                    } else {
                        this.prevSlide();
                    }
                    break;
                case 'Home':
                    e.preventDefault(); this.goToSlide(1); break;
                case 'End':
                    e.preventDefault(); this.goToSlide(this.totalSlides); break;
            }
        });
    }

    bindTouch() {
        let startX = 0, startY = 0, startScrollTop = 0;

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

            if (absX > absY && absX > 50 && absY < 80) {
                const active = this.getActiveSlide();
                const scrolled = active ? Math.abs(active.scrollTop - startScrollTop) : 0;
                if (scrolled < 10) {
                    diffX > 0 ? this.nextSlide() : this.prevSlide();
                }
            }
        }, { passive: true });
    }

    bindWheel() {
        let wheelLock = false;
        document.addEventListener('wheel', (e) => {
            if (wheelLock) return;
            const active = this.getActiveSlide();
            if (!active) return;

            const atBottom = active.scrollTop + active.clientHeight >= active.scrollHeight - 4;
            const atTop    = active.scrollTop <= 4;

            if (e.deltaY > 0 && atBottom) {
                wheelLock = true;
                this.nextSlide();
                setTimeout(() => { wheelLock = false; }, 800);
            } else if (e.deltaY < 0 && atTop) {
                wheelLock = true;
                this.prevSlide();
                setTimeout(() => { wheelLock = false; }, 800);
            }
        }, { passive: true });
    }

    prevSlide() {
        if (this.currentSlide > 1) this.goToSlide(this.currentSlide - 1);
    }

    nextSlide() {
        if (this.currentSlide < this.totalSlides) this.goToSlide(this.currentSlide + 1);
    }

    goToSlide(num) {
        if (num < 1 || num > this.totalSlides) return;
        this.currentSlide = num;
        this.positionSlides();
        this.updateUI();
        setTimeout(() => {
            const active = this.getActiveSlide();
            if (active) active.scrollTop = 0;
        }, 100);
    }

    updateUI() {
        if (this.currentSlideEl) this.currentSlideEl.textContent = this.currentSlide;
        const progress = (this.currentSlide / this.totalSlides) * 100;
        if (this.progressFill) this.progressFill.style.width = `${progress}%`;
        this.retriggerAnimations();
    }

    retriggerAnimations() {
        const active = document.querySelector('.slide.active');
        if (!active) return;
        active.querySelectorAll('.fade-in').forEach(el => {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window._presentation = new Presentation();
});
