// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Countdown Timer
function updateCountdown() {
    // Set target date (example: 30 days from now)
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + 30);
    
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

// Add animation styles dynamically
const style = document.createElement('style');
style.textContent = `
    .stat-card,
    .step-card,
    .benefit-card,
    .timeline-item,
    .token-item {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }
    
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
`;
document.head.appendChild(style);

// Live stats simulation (replace with real API calls)
function updateLiveStats() {
    // Simulate token sales progress
    const tokensSold = 234; // Replace with actual data
    const totalTokens = 800;
    const percentSold = Math.round((tokensSold / totalTokens) * 100);
    const totalRaised = tokensSold * 25;
    
    const tokensSoldEl = document.getElementById('tokensSold');
    const percentSoldEl = document.getElementById('percentSold');
    const totalRaisedEl = document.getElementById('totalRaised');
    
    if (tokensSoldEl) tokensSoldEl.textContent = tokensSold;
    if (percentSoldEl) percentSoldEl.textContent = percentSold;
    if (totalRaisedEl) totalRaisedEl.textContent = totalRaised.toLocaleString();
}

// Update stats on load
updateLiveStats();

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-section');
    
    if (hero && scrolled < window.innerHeight) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        hero.style.opacity = 1 - (scrolled / window.innerHeight);
    }
});

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

// Prevent default on buy buttons (add your purchase logic here)
document.querySelectorAll('a[href="#buy-now"], a[href="#comprar"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        // Add your purchase modal or redirect logic here
        alert('Función de compra en desarrollo. Contacta por WhatsApp para adquirir KIMEN tokens.');
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

// Whitepaper button
document.querySelectorAll('a[href="#whitepaper"]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        // Add your whitepaper link here
        alert('Whitepaper próximamente disponible.');
    });
});

console.log('KIMEN Token Landing Page - Loaded Successfully');
