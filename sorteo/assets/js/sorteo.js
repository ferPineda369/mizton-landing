// JavaScript para el Sistema de Sorteo Mizton - Versión Navideña
class SorteoApp {
    constructor() {
        this.selectedNumbers = [];
        this.blockedNumbers = new Set();
        this.blockingTimer = null;
        this.reservationTimer = null;
        this.countdownTimer = null;
        this.targetDate = new Date('2025-11-28T23:59:59').getTime();
        this.snowflakes = [];
        this.whatsappGroupUrl = 'https://chat.whatsapp.com/XXXXXXXXXXXXXXX'; // Cambiar por URL real
        this.blockingTimeLeft = 0;
        this.usingFallbackAPI = false; // Para detectar si estamos usando API de respaldo
        this.previouslySelectedNumbers = []; // Para rastrear números previamente seleccionados
        
        this.init();
    }
    
    init() {
        this.createChristmasEffects();
        this.startCountdown();
        this.loadNumbers();
        this.setupEventListeners();
        this.startSnowfall();
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
            console.log('Cargando números desde API...');
            
            // Usar API corregida para debug
            const response = await fetch('api/get_numbers_fixed.php');
            
            console.log('Respuesta recibida:', response.status, response.statusText);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Datos recibidos:', data);
            
            if (data.success) {
                this.renderNumbers(data.numbers);
            } else {
                console.error('Error al cargar números:', data.message);
                console.log('Intentando con API simplificada...');
                
                // Fallback a API simplificada
                try {
                    const fallbackResponse = await fetch('api/get_numbers_simple.php');
                    const fallbackData = await fallbackResponse.json();
                    
                    if (fallbackData.success) {
                        this.usingFallbackAPI = true; // Marcar que estamos usando fallback
                        this.renderNumbers(fallbackData.numbers);
                        // No mostrar mensaje de respaldo, funciona transparentemente
                    } else {
                        this.showAlert('Error al cargar los números. Por favor, recarga la página.', 'danger');
                    }
                } catch (fallbackError) {
                    this.showAlert('Error al cargar los números. Por favor, recarga la página.', 'danger');
                }
            }
        } catch (error) {
            console.error('Error al cargar números:', error);
            
            console.log('Error principal, intentando con API simplificada...');
            
            // Intentar con API simplificada como último recurso
            try {
                const fallbackResponse = await fetch('api/get_numbers_simple.php');
                const fallbackData = await fallbackResponse.json();
                
                if (fallbackData.success) {
                    this.usingFallbackAPI = true; // Marcar que estamos usando fallback
                    this.renderNumbers(fallbackData.numbers);
                    // Funciona transparentemente sin mensaje al usuario
                    return;
                }
            } catch (fallbackError) {
                console.error('También falló la API de respaldo:', fallbackError);
            }
            
            // Mostrar error más específico
            let errorMessage = 'Error de conexión. ';
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorMessage += 'No se puede conectar al servidor. Verifica que el servidor esté ejecutándose.';
            } else if (error.message) {
                errorMessage += error.message;
            } else {
                errorMessage += 'Por favor, verifica tu conexión a internet.';
            }
            
            this.showAlert(errorMessage, 'danger');
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
                card.addEventListener('click', () => this.toggleNumber(number.number_value, card));
                
                // Agregar efecto de brillo navideño al hacer hover
                card.addEventListener('mouseenter', () => {
                    this.addChristmasSparkle(card);
                });
            }
            
            // Marcar números ya seleccionados
            if (this.selectedNumbers.includes(number.number_value)) {
                card.classList.add('number-selected');
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
    
    // Alternar selección de número (selección múltiple)
    toggleNumber(number, cardElement) {
        // Verificar si el número está bloqueado por otro usuario
        if (this.blockedNumbers.has(number) && !this.selectedNumbers.includes(number)) {
            this.showAlert('Ese número está siendo reservado por otro usuario. Puedes esperar 2 minutos para confirmar si fue comprado o elegir otro número disponible.', 'warning');
            return;
        }
        
        const index = this.selectedNumbers.indexOf(number);
        
        if (index > -1) {
            // Deseleccionar número
            this.selectedNumbers.splice(index, 1);
            cardElement.classList.remove('number-selected');
            
            // Actualizar bloqueos en servidor de forma inteligente
            this.updateBlocksOnServer();
            
            // Si no hay más números seleccionados, limpiar timer
            if (this.selectedNumbers.length === 0) {
                this.clearBlockingTimer();
            }
        } else {
            // Seleccionar número
            this.selectedNumbers.push(number);
            cardElement.classList.add('number-selected');
            
            // Actualizar bloqueos en servidor de forma inteligente
            this.updateBlocksOnServer();
            
            // Iniciar timer si es el primer número
            if (this.selectedNumbers.length === 1) {
                this.startBlockingTimer();
            }
        }
        
        // Actualizar botón de continuar y total
        this.updateContinueButton();
        this.updateTotalAmount();
    }
    
    // Actualizar botón de continuar
    updateContinueButton() {
        let continueBtn = document.getElementById('continueBtn');
        
        if (!continueBtn) {
            // Crear botón si no existe
            continueBtn = document.createElement('button');
            continueBtn.id = 'continueBtn';
            continueBtn.className = 'btn btn-success btn-lg position-fixed';
            continueBtn.style.cssText = `
                bottom: 20px;
                right: 20px;
                z-index: 1050;
                border-radius: 50px;
                padding: 15px 25px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            `;
            continueBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Continuar';
            continueBtn.addEventListener('click', () => this.showRegistrationModal());
            document.body.appendChild(continueBtn);
        }
        
        if (this.selectedNumbers.length > 0) {
            continueBtn.style.display = 'block';
            continueBtn.innerHTML = `<i class="fas fa-arrow-right"></i> Continuar (${this.selectedNumbers.length} números)`;
        } else {
            continueBtn.style.display = 'none';
        }
    }
    
    // Mostrar modal de registro
    showRegistrationModal() {
        if (this.selectedNumbers.length === 0) {
            this.showAlert('Por favor selecciona al menos un número', 'warning');
            return;
        }
        
        // Actualizar información del modal
        document.getElementById('selectedNumber').textContent = this.selectedNumbers.join(', ');
        document.getElementById('selectedNumbersList').textContent = this.selectedNumbers.join(', ');
        document.getElementById('selectedNumbers').value = JSON.stringify(this.selectedNumbers);
        
        // Actualizar concepto de pago
        const conceptText = this.selectedNumbers.length === 1 
            ? `Sorteo Número ${this.selectedNumbers[0]}`
            : `Sorteo Números ${this.selectedNumbers.join(', ')}`;
        
        document.querySelectorAll('.payment-number').forEach(el => {
            el.textContent = this.selectedNumbers.join(', ');
        });
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('registrationModal'));
        modal.show();
        
        // Limpiar formulario pero mantener números seleccionados
        document.getElementById('fullName').value = '';
        
        // Actualizar total
        this.updateTotalAmount();
    }
    
    // Actualizar monto total
    updateTotalAmount() {
        const totalElement = document.getElementById('totalAmount');
        if (totalElement) {
            const total = this.selectedNumbers.length * 25;
            totalElement.textContent = total.toFixed(2);
        }
    }
    
    // Iniciar timer de bloqueo temporal (2 minutos)
    startBlockingTimer() {
        // No mostrar timer si estamos usando API de respaldo
        if (this.usingFallbackAPI) return;
        
        this.clearBlockingTimer();
        this.blockingTimeLeft = 120; // 2 minutos en segundos
        
        // Mostrar alerta de bloqueo
        const blockingAlert = document.getElementById('blockingTimer');
        if (blockingAlert) {
            blockingAlert.style.display = 'block';
        }
        
        this.blockingTimer = setInterval(() => {
            const minutes = Math.floor(this.blockingTimeLeft / 60);
            const seconds = this.blockingTimeLeft % 60;
            
            const timeElement = document.getElementById('blockingTimeLeft');
            if (timeElement) {
                timeElement.textContent = `${minutes}:${String(seconds).padStart(2, '0')}`;
                
                // Cambiar color según tiempo restante
                if (this.blockingTimeLeft <= 30) {
                    timeElement.className = 'timer-danger';
                } else if (this.blockingTimeLeft <= 60) {
                    timeElement.className = 'timer-warning';
                }
            }
            
            this.blockingTimeLeft--;
            
            if (this.blockingTimeLeft < 0) {
                this.clearBlockingTimer();
                
                // Limpiar selección y actualizar bloqueos
                this.selectedNumbers = [];
                this.updateBlocksOnServer(); // Esto desbloqueará todos los números previamente seleccionados
                
                this.showAlert('Tiempo de bloqueo agotado. Los números han sido liberados.', 'warning');
                this.updateContinueButton();
                this.loadNumbers(); // Recargar para actualizar estados
            }
        }, 1000);
    }
    
    // Limpiar timer de bloqueo
    clearBlockingTimer() {
        if (this.blockingTimer) {
            clearInterval(this.blockingTimer);
            this.blockingTimer = null;
        }
        
        const blockingAlert = document.getElementById('blockingTimer');
        if (blockingAlert) {
            blockingAlert.style.display = 'none';
        }
        
        const timeElement = document.getElementById('blockingTimeLeft');
        if (timeElement) {
            timeElement.textContent = '2:00';
            timeElement.className = '';
        }
        
        this.blockingTimeLeft = 0;
    }
    
    // Actualizar bloqueos en el servidor (más inteligente)
    async updateBlocksOnServer() {
        if (this.usingFallbackAPI) return;
        
        // Determinar qué números bloquear y desbloquear
        const numbersToBlock = this.selectedNumbers.filter(num => !this.previouslySelectedNumbers.includes(num));
        const numbersToUnblock = this.previouslySelectedNumbers.filter(num => !this.selectedNumbers.includes(num));
        
        try {
            // Si hay números para desbloquear, hacerlo primero
            if (numbersToUnblock.length > 0) {
                await fetch('api/block_numbers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        numbers: numbersToUnblock,
                        action: 'unblock',
                        session_id: this.getSessionId()
                    })
                });
            }
            
            // Si hay números para bloquear, hacerlo después
            if (numbersToBlock.length > 0) {
                const response = await fetch('api/block_numbers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        numbers: numbersToBlock,
                        action: 'block',
                        session_id: this.getSessionId()
                    })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    this.showAlert(data.message, 'warning');
                    // Si hay conflicto, recargar números
                    this.loadNumbers();
                }
            }
            
            // Actualizar el registro de números previamente seleccionados
            this.previouslySelectedNumbers = [...this.selectedNumbers];
            
        } catch (error) {
            console.error('Error actualizando bloqueos:', error);
        }
    }
    
    // Bloquear números en el servidor (función legacy mantenida para compatibilidad)
    async blockNumbersOnServer() {
        if (this.selectedNumbers.length === 0 || this.usingFallbackAPI) return;
        
        try {
            const response = await fetch('api/block_numbers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    numbers: this.selectedNumbers,
                    action: 'block',
                    session_id: this.getSessionId()
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                this.showAlert(data.message, 'warning');
                // Si hay conflicto, recargar números
                this.loadNumbers();
            }
        } catch (error) {
            console.error('Error bloqueando números:', error);
        }
    }
    
    // Desbloquear números en el servidor
    async unblockNumbersOnServer() {
        if (this.usingFallbackAPI) return;
        
        try {
            const response = await fetch('api/block_numbers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    numbers: [],
                    action: 'unblock',
                    session_id: this.getSessionId()
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                console.error('Error desbloqueando números:', data.message);
            }
        } catch (error) {
            console.error('Error desbloqueando números:', error);
        }
    }
    
    // Obtener ID de sesión (simplificado)
    getSessionId() {
        let sessionId = localStorage.getItem('sorteo_session_id');
        if (!sessionId) {
            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('sorteo_session_id', sessionId);
        }
        return sessionId;
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
        
        // Validar que hay números seleccionados
        if (this.selectedNumbers.length === 0) {
            this.showAlert('No hay números seleccionados', 'warning');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Procesando...';
        
        try {
            // Registrar cada número seleccionado
            const registrationPromises = this.selectedNumbers.map(number => {
                const numberFormData = new FormData();
                numberFormData.append('number', number);
                numberFormData.append('fullName', formData.get('fullName'));
                
                const apiUrl = this.usingFallbackAPI ? 'api/register_number_simple.php' : 'api/register_number.php';
                return fetch(apiUrl, {
                    method: 'POST',
                    body: numberFormData
                });
            });
            
            const responses = await Promise.all(registrationPromises);
            const results = await Promise.all(responses.map(r => r.json()));
            
            const successCount = results.filter(r => r.success).length;
            const failCount = results.length - successCount;
            
            if (successCount > 0) {
                const message = failCount > 0 
                    ? `${successCount} números reservados exitosamente. ${failCount} números no pudieron reservarse.`
                    : `¡${successCount} números reservados exitosamente! Tienes 15 minutos para confirmar el pago.`;
                
                this.showAlert(message, successCount === results.length ? 'success' : 'warning');
                this.startReservationTimer(15 * 60); // 15 minutos
                this.loadNumbers(); // Recargar números
                
                // Abrir WhatsApp
                this.openWhatsAppGroup();
                
                // Limpiar bloqueo temporal
                this.clearBlockingTimer();
                
                // Limpiar selección y actualizar bloqueos
                this.selectedNumbers = [];
                this.updateBlocksOnServer(); // Esto desbloqueará todos los números
                this.updateContinueButton();
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('registrationModal')).hide();
                }, 2000);
                
            } else {
                this.showAlert('No se pudo reservar ningún número. Intenta nuevamente.', 'danger');
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
    
    // Abrir grupo de WhatsApp
    openWhatsAppGroup() {
        // Abrir en nueva pestaña
        window.open(this.whatsappGroupUrl, '_blank');
        
        // Mostrar mensaje informativo
        this.showAlert('Se ha abierto el grupo de WhatsApp. ¡No olvides unirte para participar en el sorteo en vivo!', 'info');
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
    
    // ========== EFECTOS NAVIDEÑOS ==========
    
    // Crear efectos navideños
    createChristmasEffects() {
        // Crear contenedor de copos de nieve
        const snowContainer = document.createElement('div');
        snowContainer.className = 'snowflakes';
        document.body.appendChild(snowContainer);
        
        // Crear luces navideñas
        const lights = document.createElement('div');
        lights.className = 'christmas-lights';
        document.body.appendChild(lights);
        
        this.snowContainer = snowContainer;
    }
    
    // Iniciar caída de nieve
    startSnowfall() {
        // Crear copos de nieve iniciales
        for (let i = 0; i < 50; i++) {
            this.createSnowflake();
        }
        
        // Crear nuevos copos periódicamente
        setInterval(() => {
            this.createSnowflake();
        }, 300);
        
        // Limpiar copos antiguos
        setInterval(() => {
            this.cleanupSnowflakes();
        }, 5000);
    }
    
    // Crear un copo de nieve individual
    createSnowflake() {
        const snowflake = document.createElement('div');
        snowflake.className = 'snowflake';
        
        // Diferentes símbolos de copos de nieve
        const snowSymbols = ['❄', '❅', '❆', '✻', '✼', '❋', '✱', '✲'];
        snowflake.textContent = snowSymbols[Math.floor(Math.random() * snowSymbols.length)];
        
        // Posición aleatoria
        snowflake.style.left = Math.random() * 100 + '%';
        snowflake.style.animationDuration = (Math.random() * 4 + 3) + 's';
        snowflake.style.opacity = Math.random() * 0.8 + 0.2;
        snowflake.style.fontSize = (Math.random() * 15 + 15) + 'px';
        
        // Delay aleatorio para que no todos caigan al mismo tiempo
        snowflake.style.animationDelay = Math.random() * 2 + 's';
        
        this.snowContainer.appendChild(snowflake);
        this.snowflakes.push(snowflake);
        
        // Remover el copo después de que termine la animación
        setTimeout(() => {
            if (snowflake.parentNode) {
                snowflake.parentNode.removeChild(snowflake);
            }
        }, 8000);
    }
    
    // Limpiar copos de nieve antiguos
    cleanupSnowflakes() {
        this.snowflakes = this.snowflakes.filter(snowflake => {
            if (!snowflake.parentNode) {
                return false;
            }
            return true;
        });
        
        // Mantener un máximo de 100 copos
        if (this.snowflakes.length > 100) {
            const excess = this.snowflakes.splice(0, this.snowflakes.length - 100);
            excess.forEach(snowflake => {
                if (snowflake.parentNode) {
                    snowflake.parentNode.removeChild(snowflake);
                }
            });
        }
    }
    
    // Efecto de brillo navideño en números
    addChristmasSparkle(element) {
        const sparkle = document.createElement('div');
        sparkle.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 4px;
            background: #FFD700;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: sparkle 1s ease-out forwards;
            pointer-events: none;
            z-index: 10;
        `;
        
        element.style.position = 'relative';
        element.appendChild(sparkle);
        
        setTimeout(() => {
            if (sparkle.parentNode) {
                sparkle.parentNode.removeChild(sparkle);
            }
        }, 1000);
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
    
    @keyframes sparkle {
        0% {
            transform: translate(-50%, -50%) scale(0);
            opacity: 1;
        }
        50% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
        }
    }
    
    .alert-floating {
        animation: slideInRight 0.3s ease-out;
    }
    
    /* Efectos adicionales de partículas navideñas */
    @keyframes twinkle {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    
    .christmas-particle {
        position: absolute;
        width: 2px;
        height: 2px;
        background: #FFD700;
        border-radius: 50%;
        animation: twinkle 2s infinite;
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
