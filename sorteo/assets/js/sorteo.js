// JavaScript para el Sistema de Sorteo Mizton
class SorteoApp {
    constructor() {
        this.selectedNumber = null;
        this.reservationTimer = null;
        this.countdownTimer = null;
        this.targetDate = new Date('2025-11-28T23:59:59').getTime();
        
        this.init();
    }
    
    init() {
        this.startCountdown();
        this.loadNumbers();
        this.setupEventListeners();
    }
    
    // Contador regresivo al 28 de noviembre 2025
    startCountdown() {
        this.countdownTimer = setInterval(() => {
            const now = new Date().getTime();
            const distance = this.targetDate - now;
            
            if (distance < 0) {
                clearInterval(this.countdownTimer);
                document.getElementById('countdown').innerHTML = '<div class="text-center"><h3>¡El sorteo ha finalizado!</h3></div>';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }, 1000);
    }
    
    // Cargar números desde la base de datos
    async loadNumbers() {
        try {
            const response = await fetch('api/get_numbers.php');
            const data = await response.json();
            
            if (data.success) {
                this.renderNumbers(data.numbers);
            } else {
                console.error('Error al cargar números:', data.message);
                this.showAlert('Error al cargar los números. Por favor, recarga la página.', 'danger');
            }
        } catch (error) {
            console.error('Error de conexión:', error);
            this.showAlert('Error de conexión. Por favor, verifica tu conexión a internet.', 'danger');
        }
    }
    
    // Renderizar grid de números
    renderNumbers(numbers) {
        const grid = document.getElementById('numbersGrid');
        grid.innerHTML = '';
        
        numbers.forEach(number => {
            const card = document.createElement('div');
            card.className = `number-card number-${number.status}`;
            card.textContent = number.number_value;
            card.dataset.number = number.number_value;
            card.dataset.status = number.status;
            
            if (number.status === 'available') {
                card.addEventListener('click', () => this.selectNumber(number.number_value));
            }
            
            // Tooltip para números ocupados
            if (number.status === 'confirmed') {
                card.title = `Número ocupado por ${number.participant_name}`;
            } else if (number.status === 'reserved') {
                card.title = 'Número reservado temporalmente';
            }
            
            grid.appendChild(card);
        });
    }
    
    // Seleccionar número
    selectNumber(number) {
        this.selectedNumber = number;
        document.getElementById('selectedNumber').textContent = number;
        document.getElementById('numberInput').value = number;
        document.querySelectorAll('.payment-number').forEach(el => {
            el.textContent = number;
        });
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('registrationModal'));
        modal.show();
        
        // Limpiar formulario
        document.getElementById('registrationForm').reset();
        document.getElementById('numberInput').value = number;
    }
    
    // Configurar event listeners
    setupEventListeners() {
        // Formulario de registro
        document.getElementById('registrationForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitRegistration();
        });
        
        // Limpiar timer al cerrar modal
        document.getElementById('registrationModal').addEventListener('hidden.bs.modal', () => {
            this.clearReservationTimer();
        });
    }
    
    // Enviar registro
    async submitRegistration() {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Procesando...';
        
        try {
            const response = await fetch('api/register_number.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showAlert('¡Número reservado exitosamente! Tienes 15 minutos para confirmar el pago.', 'success');
                this.startReservationTimer(15 * 60); // 15 minutos
                this.loadNumbers(); // Recargar números
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('registrationModal')).hide();
                }, 2000);
                
            } else {
                this.showAlert(data.message || 'Error al reservar el número', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión. Por favor, intenta nuevamente.', 'danger');
        } finally {
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Participación';
        }
    }
    
    // Timer de reserva (15 minutos)
    startReservationTimer(seconds) {
        this.clearReservationTimer();
        
        const timerElement = document.getElementById('reservationTimer');
        let timeLeft = seconds;
        
        this.reservationTimer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            
            timerElement.textContent = `${minutes}:${String(secs).padStart(2, '0')}`;
            
            // Cambiar color según tiempo restante
            if (timeLeft <= 300) { // 5 minutos
                timerElement.className = 'timer-warning';
            }
            if (timeLeft <= 60) { // 1 minuto
                timerElement.className = 'timer-danger';
            }
            
            timeLeft--;
            
            if (timeLeft < 0) {
                clearInterval(this.reservationTimer);
                timerElement.textContent = '¡Tiempo agotado!';
                this.showAlert('El tiempo de reserva ha expirado. El número está disponible nuevamente.', 'warning');
                this.loadNumbers(); // Recargar números
            }
        }, 1000);
    }
    
    // Limpiar timer de reserva
    clearReservationTimer() {
        if (this.reservationTimer) {
            clearInterval(this.reservationTimer);
            this.reservationTimer = null;
        }
        
        const timerElement = document.getElementById('reservationTimer');
        if (timerElement) {
            timerElement.textContent = '15:00';
            timerElement.className = '';
        }
    }
    
    // Mostrar alertas
    showAlert(message, type = 'info') {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.alert-floating');
        existingAlerts.forEach(alert => alert.remove());
        
        // Crear nueva alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-floating`;
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
        `;
        
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
    
    // Actualizar números periódicamente
    startPeriodicUpdate() {
        setInterval(() => {
            this.loadNumbers();
        }, 30000); // Cada 30 segundos
    }
}

// Inicializar aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    const app = new SorteoApp();
    app.startPeriodicUpdate();
});

// Animaciones CSS adicionales
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .alert-floating {
        animation: slideInRight 0.3s ease-out;
    }
`;
document.head.appendChild(style);

// Funciones de utilidad
window.SorteoUtils = {
    // Formatear fecha
    formatDate: (date) => {
        return new Intl.DateTimeFormat('es-MX', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    },
    
    // Validar email
    validateEmail: (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Validar nombre
    validateName: (name) => {
        return name.trim().length >= 3 && /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(name.trim());
    }
};
