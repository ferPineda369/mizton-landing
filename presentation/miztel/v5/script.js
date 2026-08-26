/* ==========================================================================
   MIZTEL PRESENTATION v5 - JAVASCRIPT
   Motor crossfade + placeholder de imágenes + retrigger de la cascada FX
   ========================================================================== */

class PresentationV5 {
    constructor() {
        this.currentSlide = 1;
        this.totalSlides = 17;
        this.slides = document.querySelectorAll('.slide');
        this.currentSlideEl = document.getElementById('current-slide');
        this.totalSlidesEl  = document.getElementById('total-slides');
        this.progressFill   = document.getElementById('progress-fill');

        this.init();
    }

    init() {
        if (this.totalSlidesEl) this.totalSlidesEl.textContent = this.totalSlides;
        this.setupImagePlaceholders();
        this.buildCrowds();
        this.updateUI();
        this.bindKeyboard();
        this.bindTouch();
        this.bindWheel();
    }

    /* Campo de puntos-persona: ~48% "apagados" (sin acceso a internet) */
    buildCrowds() {
        document.querySelectorAll('.crowd').forEach(field => {
            const total = 84;
            const offCount = Math.round(total * 0.48);
            const offSet = new Set();
            while (offSet.size < offCount) {
                offSet.add(Math.floor(Math.random() * total));
            }
            for (let i = 0; i < total; i++) {
                const dot = document.createElement('i');
                if (offSet.has(i)) dot.classList.add('off');
                dot.style.animationDelay = `${i * 0.018}s`;
                field.appendChild(dot);
            }
        });
    }

    /* Si una imagen no existe, la reemplaza por un placeholder
       que indica el archivo esperado y la imagen sugerida. */
    setupImagePlaceholders() {
        document.querySelectorAll('img.ph-img').forEach(img => {
            img.addEventListener('error', () => {
                const ph = document.createElement('div');
                ph.className = 'img-ph';
                ph.innerHTML = `
                    <div class="ph-icon">🖼️</div>
                    <div class="ph-label">IMAGEN SUGERIDA · ${img.getAttribute('src')}</div>
                    <div class="ph-desc">${img.dataset.desc || img.alt}</div>
                `;
                img.replaceWith(ph);
            }, { once: true });
            if (img.complete && img.naturalWidth === 0) {
                img.dispatchEvent(new Event('error'));
            }
        });
    }

    getActiveSlide() {
        return document.querySelector('.slide.active');
    }

    canScrollDown() {
        const s = this.getActiveSlide();
        return s ? (s.scrollHeight > s.clientHeight && s.scrollTop + s.clientHeight < s.scrollHeight - 2) : false;
    }

    canScrollUp() {
        const s = this.getActiveSlide();
        return s ? s.scrollTop > 2 : false;
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
            const diffX = startX - e.changedTouches[0].clientX;
            const diffY = startY - e.changedTouches[0].clientY;
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
                setTimeout(() => { wheelLock = false; }, 900);
            } else if (e.deltaY < 0 && atTop) {
                wheelLock = true;
                this.prevSlide();
                setTimeout(() => { wheelLock = false; }, 900);
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
        if (num < 1 || num > this.totalSlides || num === this.currentSlide) return;

        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index + 1 === num);
        });

        this.currentSlide = num;
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
        this.retriggerFX();
    }

    /* Reinicia la cascada de ideas al entrar a cada lámina */
    retriggerFX() {
        const active = this.getActiveSlide();
        if (!active) return;
        active.querySelectorAll('.fx, .anim, .draw').forEach(el => {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window._presentation = new PresentationV5();
});
