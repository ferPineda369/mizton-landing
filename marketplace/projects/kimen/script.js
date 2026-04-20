// Navbar: scroll effect + mobile toggle
(function () {
    const navbar = document.getElementById('kimen-navbar');
    const toggle = document.getElementById('kimen-nav-toggle');
    const mobileMenu = document.getElementById('kimen-mobile-menu');

    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    if (toggle && mobileMenu) {
        toggle.addEventListener('click', function () {
            mobileMenu.classList.toggle('open');
        });

        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileMenu.classList.remove('open');
            });
        });
    }
})();

// Smooth scroll for anchor links (solo anchors internos válidos)
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        // Ignorar # vacío o links que no sean anchors internos
        if (!href || href === '#' || href.length <= 1) {
            e.preventDefault();
            return;
        }
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const navbarHeight = 64;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Countdown Timer
function updateCountdown() {
    // Set target date: June 15, 2026
    const targetDate = new Date('2026-06-15T23:59:59');
    
    const now = new Date().getTime();
    const distance = targetDate - now;
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('days').textContent = String(days).padStart(2, '0');
    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    
    if (distance < 0) {
        document.getElementById('countdown').innerHTML = '<p>¡Venta Finalizada!</p>';
    }
}

// Update countdown every second
setInterval(updateCountdown, 1000);
updateCountdown();

// Tokenomics Chart
function createTokenomicsChart() {
    const canvas = document.getElementById('tokenomicsChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(centerX, centerY) - 10;
    
    // Data
    const data = [
        { label: 'Venta Pública', value: 16.67, color: '#FFD700' },
        { label: 'Autor/Creador', value: 45, color: '#9B59B6' },
        { label: 'Marketing', value: 20, color: '#3498DB' },
        { label: 'Reservas Saga', value: 18.33, color: '#E74C3C' }
    ];
    
    let currentAngle = -Math.PI / 2;
    
    data.forEach(segment => {
        const sliceAngle = (segment.value / 100) * 2 * Math.PI;
        
        // Draw slice
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
        ctx.closePath();
        ctx.fillStyle = segment.color;
        ctx.fill();
        
        // Draw border
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        currentAngle += sliceAngle;
    });
    
    // Draw center circle for donut effect
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius * 0.5, 0, 2 * Math.PI);
    ctx.fillStyle = '#1a1a2e';
    ctx.fill();
}

// Initialize chart when DOM is loaded
window.addEventListener('load', () => {
    const canvas = document.getElementById('tokenomicsChart');
    if (canvas) {
        canvas.width = 400;
        canvas.height = 400;
        createTokenomicsChart();
    }
});

// Animate stats on scroll
const observerOptions = {
    threshold: 0.3,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe all cards and timeline items
document.addEventListener('DOMContentLoaded', () => {
    const animateElements = document.querySelectorAll(
        '.stat-card, .step-card, .benefit-card, .timeline-item, .token-item'
    );
    
    animateElements.forEach(el => {
        observer.observe(el);
    });
});


// Live stats (replace with real API calls when ready)
function updateLiveStats() {
    const tokensSold = 34;
    const totalTokens = 800;
    const percentSold = Math.round((tokensSold / totalTokens) * 100);
    const totalRaised = 850;
    
    const tokensSoldEl = document.getElementById('tokensSold');
    const percentSoldEl = document.getElementById('percentSold');
    const totalRaisedEl = document.getElementById('totalRaised');
    
    if (tokensSoldEl) tokensSoldEl.textContent = tokensSold;
    if (percentSoldEl) percentSoldEl.textContent = percentSold;
    if (totalRaisedEl) totalRaisedEl.textContent = totalRaised.toLocaleString();
}

// Update stats on load
updateLiveStats();

// Parallax effect removed - was causing slow/transparent effect

// Add hover effect to book cover
const bookCover = document.querySelector('.book-cover');
if (bookCover) {
    bookCover.addEventListener('mousemove', (e) => {
        const rect = bookCover.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        
        bookCover.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
    });
    
    bookCover.addEventListener('mouseleave', () => {
        bookCover.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
    });
}

// Buy buttons – #buy-now scrolls to #obtener section
document.querySelectorAll('a[href="#buy-now"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const obtener = document.getElementById('obtener');
        if (obtener) {
            const navbarHeight = 64;
            const targetPosition = obtener.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
    });
});

// Download chapter button
document.querySelectorAll('a[href="#download"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        // Add your download logic here
        alert('Descarga del capítulo 1 próximamente disponible.');
    });
});

// Whitepaper button – links directly to whitepaper.php

console.log('KIMEN Token Landing Page - Loaded Successfully');
