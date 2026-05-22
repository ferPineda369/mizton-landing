// Smart Contracts Presentation - Navigation Script

let currentSlide = 1;
const totalSlides = 7;

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
