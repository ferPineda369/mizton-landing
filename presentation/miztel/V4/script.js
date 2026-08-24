/* ==========================================================================
   MIZTEL PRESENTATION v4 - CINEMATIC ENGINE
   Crossfade + zoom de cine, contador animado, campo de puntos, retrigger FX
   ========================================================================== */

class CinematicPresentation {
    constructor() {
        this.currentSlide = 1;
        this.totalSlides = 8;
        this.slides = document.querySelectorAll('.slide');
        this.currentSlideEl = document.getElementById('current-slide');
        this.totalSlidesEl  = document.getElementById('total-slides');
        this.progressFill   = document.getElementById('progress-fill');
        this.counterTimer   = null;

        this.init();
    }

    init() {
        if (this.totalSlidesEl) this.totalSlidesEl.textContent = this.totalSlides;

        // Barras letterbox entran con la apertura
        setTimeout(() => document.body.classList.add('cine-on'), 400);

        this.updateUI();
        this.bindKeyboard();
        this.bindTouch();
        this.bindWheel();
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
                setTimeout(() => { wheelLock = false; }, 1000);
            } else if (e.deltaY < 0 && atTop) {
                wheelLock = true;
                this.prevSlide();
                setTimeout(() => { wheelLock = false; }, 1000);
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
        this.onSceneEnter(this.currentSlide);
    }

    /* Reinicia todas las animaciones de entrada del slide activo */
    retriggerFX() {
        const active = this.getActiveSlide();
        if (!active) return;
        active.querySelectorAll('.fx, .draw, .tachado').forEach(el => {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.animation = '';
        });
    }

    onSceneEnter(num) {
        if (num === 2) {
            this.runCounter();
            this.buildDotsField();
        }
    }

    /* Contador 0 → 132 */
    runCounter() {
        const el = document.getElementById('counter-132');
        if (!el) return;

        if (this.counterTimer) cancelAnimationFrame(this.counterTimer);

        const target = 132;
        const duration = 1500;
        const startDelay = 400;
        const t0 = performance.now();

        const tick = (now) => {
            const elapsed = now - t0 - startDelay;
            if (elapsed < 0) {
                el.textContent = '0';
                this.counterTimer = requestAnimationFrame(tick);
                return;
            }
            const p = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(eased * target);
            if (p < 1) {
                this.counterTimer = requestAnimationFrame(tick);
            } else {
                el.textContent = target;
            }
        };
        this.counterTimer = requestAnimationFrame(tick);
    }

    /* Campo de puntos: mitad conectados, mitad apagados */
    buildDotsField() {
        const field = document.getElementById('dots-field');
        if (!field) return;
        field.innerHTML = '';

        const total = 96;
        const offCount = Math.round(total * 0.48);

        // Índices que quedarán "apagados" (distribuidos)
        const offSet = new Set();
        while (offSet.size < offCount) {
            offSet.add(Math.floor(Math.random() * total));
        }

        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'dot-person' + (offSet.has(i) ? ' off' : '');
            dot.style.animationDelay = `${1.1 + i * 0.022}s`;
            field.appendChild(dot);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window._presentation = new CinematicPresentation();
});
