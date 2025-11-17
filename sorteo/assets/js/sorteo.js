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
        this.whatsappGroupUrl = 'https://chat.whatsapp.com/JFCyYfRXYJVJkiLsVj2yhK?mode=wwt'; // Cambiar por URL real
        this.blockingTimeLeft = 0;
        this.usingFallbackAPI = false; // Para detectar si estamos usando API de respaldo
        this.previouslySelectedNumbers = []; // Para rastrear números previamente seleccionados
        this.snowContainer = null;
        this.numbersRefreshInterval = null; // Para el refresco automático
        this.updateBlocksTimeout = null; // Para debounce de actualizaciones
        
        this.init();
    }
    
    init() {
        this.createChristmasEffects();
        this.startCountdown();
        this.loadNumbers();
        this.setupEventListeners();
        this.startSnowfall();
        
        // Cargar datos de sesión del usuario
        this.loadUserSessionData();
        
        // Recargar números cada 5 segundos para mantener apartados actualizados
        this.numbersRefreshInterval = setInterval(() => {
            this.loadNumbers();
        }, 5000);
    }
    
    // Configurar event listeners
    setupEventListeners() {
        // Actualizar concepto de pago cuando cambie el número celular
        const phoneInput = document.getElementById('phoneNumber');
        if (phoneInput) {
            phoneInput.addEventListener('input', (e) => {
                this.updatePaymentConcept(e.target.value);
            });
        }
        
        // Actualizar concepto cuando se abra el modal de datos de pago
        const paymentModal = document.getElementById('paymentDataModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', () => {
                // Obtener número de teléfono de la sesión o del formulario
                const session = this.getUserSession();
                const currentPhone = phoneInput ? phoneInput.value : '';
                const sessionPhone = session ? session.phoneNumber : '';
                const phoneToUse = currentPhone || sessionPhone;
                
                if (phoneToUse) {
                    this.updatePaymentConcept(phoneToUse);
                }
            });
        }
        
        // Configurar formulario de consulta de boletos
        const consultaForm = document.getElementById('consultaForm');
        if (consultaForm) {
            consultaForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.consultarBoletos();
            });
        }
        
        // Configurar formulario de registro para prevenir submit tradicional
        const registrationForm = document.getElementById('registrationForm');
        if (registrationForm) {
            registrationForm.addEventListener('submit', (e) => {
                e.preventDefault();
                console.log('🚫 Submit del formulario interceptado - llamando submitRegistration()');
                this.submitRegistration();
            });
        }
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
            
            // Verificar si está bloqueado por otro usuario
            const isBlockedByOther = number.is_blocked_by_other || false;
            
            // Solo permitir click si:
            // 1. Está disponible Y no bloqueado por otro usuario, O
            // 2. Ya está seleccionado por este usuario (para poder deseleccionar)
            const canClick = (number.status === 'available' && !isBlockedByOther) || 
                           this.selectedNumbers.includes(number.number_value);
            
            if (canClick) {
                card.addEventListener('click', () => this.toggleNumber(number.number_value, card));
                card.style.cursor = 'pointer';
                
                // Agregar efecto de brillo navideño al hacer hover (solo si no está bloqueado por otro)
                if (!isBlockedByOther) {
                    card.addEventListener('mouseenter', () => {
                        this.addChristmasSparkle(card);
                    });
                }
            } else {
                // Número no clickeable
                card.style.cursor = 'not-allowed';
            }
            
            // Marcar números ya seleccionados por este usuario
            if (this.selectedNumbers.includes(number.number_value)) {
                card.classList.add('number-selected');
                // Asegurar que puede hacer click para deseleccionar
                card.style.cursor = 'pointer';
            }
            
            // Marcar números bloqueados por otros usuarios
            if (isBlockedByOther && !this.selectedNumbers.includes(number.number_value)) {
                card.classList.add('number-blocked');
                card.title = 'Número bloqueado temporalmente por otro usuario';
                this.blockedNumbers.add(number.number_value);
            } else {
                this.blockedNumbers.delete(number.number_value);
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
        
        // Verificar estado del elemento (doble verificación)
        const status = cardElement.dataset.status;
        if (status !== 'available' && !this.selectedNumbers.includes(number)) {
            this.showAlert('Este número no está disponible para selección.', 'warning');
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
        
        // Listener movido a setupEventListeners
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
    
    // Iniciar timer de apartado temporal (2 minutos)
    startBlockingTimer() {
        // No mostrar timer si estamos usando API de respaldo
        if (this.usingFallbackAPI) return;
        
        this.clearBlockingTimer();
        this.blockingTimeLeft = 120; // 2 minutos en segundos
        
        // Mostrar alerta de apartado
        const blockingAlert = document.getElementById('blockingTimer');
        if (blockingAlert) {
            blockingAlert.style.display = 'block';
        }
        
        this.blockingTimer = setInterval(() => {
            const minutes = Math.floor(this.blockingTimeLeft / 60);
            const seconds = this.blockingTimeLeft % 60;
            
            const timeElement = document.getElementById('blockingTimeLeft');
            if (timeElement) {
                timeElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
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
                this.handleBlockingTimeout();
            }
        }, 1000);
    }
    
    // Manejar timeout de bloqueo - limpiar formulario y recargar página
    handleBlockingTimeout() {
        // Cerrar modal si está abierto
        const modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
        if (modal) {
            modal.hide();
        }
        
        // Limpiar selecciones
        this.selectedNumbers = [];
        this.unblockNumbersOnServer();
        
        // Mostrar mensaje y recargar página
        this.showAlert('Tiempo de apartado expirado. La página se recargará para actualizar los números disponibles.', 'warning');
        
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }
    
    // Limpiar timer de apartado
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
            timeElement.className = '';
        }
        
        this.blockingTimeLeft = 0;
    }
    
    // Actualizar bloqueos en el servidor con debounce
    updateBlocksOnServer() {
        if (this.usingFallbackAPI) return;
        
        // Cancelar timeout anterior si existe
        if (this.updateBlocksTimeout) {
            clearTimeout(this.updateBlocksTimeout);
        }
        
        // Programar actualización con debounce de 100ms
        this.updateBlocksTimeout = setTimeout(() => {
            this.performBlocksUpdate();
        }, 100);
    }
    
    // Realizar la actualización real de bloqueos
    async performBlocksUpdate() {
        console.log('🔄 Actualizando bloqueos:', {
            selectedNumbers: this.selectedNumbers,
            previouslySelectedNumbers: this.previouslySelectedNumbers,
            sessionId: this.getSessionId()
        });
        
        try {
            // Estrategia simplificada: enviar todos los números seleccionados actuales
            const payload = {
                numbers: this.selectedNumbers,
                action: 'sync', // Nueva acción para sincronizar todos los números
                session_id: this.getSessionId()
            };
            
            console.log('📤 Enviando payload:', payload);
            
            const response = await fetch('api/block_numbers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            console.log('📥 Respuesta del servidor:', data);
            
            if (!data.success) {
                console.error('❌ Error en servidor:', data.message);
                this.showAlert(data.message, 'warning');
                // Si hay conflicto, recargar números
                this.loadNumbers();
            } else {
                console.log('✅ Bloqueos actualizados correctamente');
            }
            
            // Actualizar el registro de números previamente seleccionados
            this.previouslySelectedNumbers = [...this.selectedNumbers];
            
        } catch (error) {
            console.error('❌ Error actualizando bloqueos:', error);
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
    
    // Limpiar todos los intervalos
    cleanup() {
        if (this.numbersRefreshInterval) {
            clearInterval(this.numbersRefreshInterval);
            this.numbersRefreshInterval = null;
        }
        if (this.blockingTimer) {
            clearInterval(this.blockingTimer);
            this.blockingTimer = null;
        }
        if (this.reservationTimer) {
            clearInterval(this.reservationTimer);
            this.reservationTimer = null;
        }
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
            this.countdownTimer = null;
        }
        if (this.updateBlocksTimeout) {
            clearTimeout(this.updateBlocksTimeout);
            this.updateBlocksTimeout = null;
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
    
    // Actualizar concepto de pago con número celular
    updatePaymentConcept(phoneNumber) {
        const paymentPhoneElement = document.querySelector('.payment-phone');
        if (paymentPhoneElement) {
            if (phoneNumber && /^[0-9]{10}$/.test(phoneNumber)) {
                paymentPhoneElement.textContent = phoneNumber;
            } else {
                paymentPhoneElement.textContent = '';
            }
        }
        
        // Actualizar concepto en el modal de datos de pago
        const conceptoInput = document.getElementById('conceptoInput');
        if (conceptoInput && phoneNumber && /^[0-9]{10}$/.test(phoneNumber)) {
            conceptoInput.value = `Apoyo a Pahuata ${phoneNumber}`;
        } else if (conceptoInput) {
            conceptoInput.value = 'Apoyo a Pahuata';
        }
        
        // Actualizar concepto en el modal de registro
        const conceptoRegistro = document.getElementById('conceptoRegistro');
        if (conceptoRegistro && phoneNumber && /^[0-9]{10}$/.test(phoneNumber)) {
            conceptoRegistro.value = `Apoyo a Pahuata ${phoneNumber}`;
        } else if (conceptoRegistro) {
            conceptoRegistro.value = 'Apoyo a Pahuata';
        }
    }
    
    // Consultar boletos
    async consultarBoletos() {
        let phoneNumber = document.getElementById('consultaPhone').value.trim();
        
        // Si no hay número ingresado, usar el de la sesión
        if (!phoneNumber) {
            const session = this.getUserSession();
            if (session && session.phoneNumber) {
                phoneNumber = session.phoneNumber;
                document.getElementById('consultaPhone').value = phoneNumber;
            }
        }
        
        if (!phoneNumber) {
            this.showAlert('Por favor ingresa tu número celular', 'warning');
            return;
        }
        
        if (!/^\d{10}$/.test(phoneNumber)) {
            this.showAlert('El número celular debe tener exactamente 10 dígitos', 'warning');
            return;
        }
        
        // Mostrar loading
        const loadingDiv = document.getElementById('consultaLoading');
        const resultadosDiv = document.getElementById('consultaResultados');
        
        loadingDiv.style.display = 'block';
        resultadosDiv.style.display = 'none';
        
        try {
            const formData = new FormData();
            formData.append('phoneNumber', phoneNumber);
            
            const response = await fetch('api/consulta_boletos.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            loadingDiv.style.display = 'none';
            
            if (data.success) {
                this.mostrarResultadosConsulta(data.data);
            } else {
                this.showAlert(data.message, 'info');
            }
            
        } catch (error) {
            loadingDiv.style.display = 'none';
            console.error('Error consultando boletos:', error);
            this.showAlert('Error al consultar los boletos. Intenta de nuevo.', 'danger');
        }
    }
    
    // Mostrar resultados de la consulta
    mostrarResultadosConsulta(data) {
        const resultadosDiv = document.getElementById('consultaResultados');
        const boletosDiv = document.getElementById('boletosEncontrados');
        
        let html = `
            <div class="alert alert-success">
                <strong>¡Encontrados!</strong> Se encontraron ${data.totalBoletos} boleto(s) para el número ${data.phoneNumber}
            </div>
        `;
        
        data.boletos.forEach(boleto => {
            const estadoClass = boleto.estado === 'confirmed' ? 'success' : 'warning';
            const estadoText = boleto.estado === 'confirmed' ? 'Confirmado' : 'Reservado';
            const estadoIcon = boleto.estado === 'confirmed' ? 'check-circle' : 'clock';
            
            html += `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <h5 class="mb-0">
                                    <span class="badge bg-primary fs-6">Nº ${boleto.numero}</span>
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <strong>${boleto.participante}</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-${estadoClass}">
                                    <i class="fas fa-${estadoIcon}"></i> ${estadoText}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">
                                    ${boleto.estado === 'confirmed' 
                                        ? `Confirmado: ${boleto.fecha_confirmacion}` 
                                        : `Reservado: ${boleto.fecha_reserva}`}
                                    ${boleto.estado === 'reserved' && boleto.expira_en 
                                        ? `<br>Expira: ${boleto.expira_en}` 
                                        : ''}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Verificar si hay boletos reservados
        const reservedTickets = data.boletos ? data.boletos.filter(b => b.estado === 'reserved') : [];
        
        // Agregar leyenda para boletos reservados
        if (reservedTickets.length > 0) {
            html += `
                <div class="alert alert-warning mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6><i class="fas fa-clock"></i> ¡Te falta muy poco para conseguir tus boletos!</h6>
                            <p class="mb-0">Realiza tu pago y envíalo al administrador del grupo de WhatsApp para que te sea aprobado.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#paymentDataModal">
                                <i class="fas fa-credit-card"></i> Datos para Pagar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Agregar botón de WhatsApp si hay boletos encontrados
        if (data.boletos && data.boletos.length > 0) {
            html += `
                <div class="alert alert-info mt-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6><i class="fab fa-whatsapp"></i> ¡Únete al Grupo de WhatsApp!</h6>
                            <p class="mb-0">Como tienes boletos registrados, es <strong>REQUISITO</strong> unirse al grupo de WhatsApp para participar en el evento en vivo.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-success" onclick="openWhatsAppGroup()">
                                <i class="fab fa-whatsapp"></i> Unirse al Grupo
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        boletosDiv.innerHTML = html;
        resultadosDiv.style.display = 'block';
    }
    
    // Abrir grupo de WhatsApp
    openWhatsAppGroup() {
        const whatsappUrl = 'https://chat.whatsapp.com/JFCyYfRXYJVJkiLsVj2yhK?mode=wwt';
        
        // Mostrar mensaje antes de abrir WhatsApp
        this.showAlert('¡Reserva exitosa! Se abrirá el grupo de WhatsApp. Es REQUISITO unirse para participar.', 'success');
        
        // Abrir WhatsApp después de un pequeño delay
        setTimeout(() => {
            window.open(whatsappUrl, '_blank');
        }, 1500);
    }
    
    // Enviar registro
    async submitRegistration() {
        console.log('🚀 === INICIO submitRegistration() ===');
        
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button') || form.querySelector('button[type="button"]');
        
        // Validar que hay números seleccionados
        if (this.selectedNumbers.length === 0) {
            this.showAlert('No hay números seleccionados', 'warning');
            return;
        }
        
        // Validar número celular (obligatorio para identificación)
        const phoneNumber = formData.get('phoneNumber');
        if (!phoneNumber || !/^[0-9]{10}$/.test(phoneNumber)) {
            this.showAlert('El número celular es obligatorio y debe tener exactamente 10 dígitos sin espacios', 'warning');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Procesando...';
        
        try {
            // Debug: mostrar datos que se van a enviar
            console.log('🔍 Datos del formulario:', {
                fullName: formData.get('fullName'),
                phoneNumber: phoneNumber,
                selectedNumbers: this.selectedNumbers,
                usingFallbackAPI: this.usingFallbackAPI
            });
            
            // Registrar cada número seleccionado
            const registrationPromises = this.selectedNumbers.map(number => {
                const numberFormData = new FormData();
                numberFormData.append('number', number);
                numberFormData.append('fullName', formData.get('fullName'));
                numberFormData.append('phoneNumber', phoneNumber);
                
                // Debug: mostrar datos específicos por número
                console.log(`📤 Enviando número ${number}:`, {
                    number: number,
                    fullName: formData.get('fullName'),
                    phoneNumber: phoneNumber
                });
                
                const apiUrl = this.usingFallbackAPI ? 'api/register_number_simple.php' : 'api/register_number.php';
                console.log(`🌐 URL de API: ${apiUrl}`);
                
                return fetch(apiUrl, {
                    method: 'POST',
                    body: numberFormData
                });
            });
            
            const responses = await Promise.all(registrationPromises);
            
            // Verificar respuestas y manejar errores de JSON
            const results = await Promise.all(responses.map(async (response) => {
                try {
                    if (!response.ok) {
                        return { success: false, message: `HTTP ${response.status}: ${response.statusText}` };
                    }
                    
                    const text = await response.text();
                    
                    // Verificar si la respuesta es JSON válido
                    try {
                        return JSON.parse(text);
                    } catch (jsonError) {
                        console.error('Respuesta no es JSON válido:', text);
                        return { 
                            success: false, 
                            message: 'Error del servidor - respuesta inválida',
                            debug: text.substring(0, 200) // Primeros 200 caracteres para debug
                        };
                    }
                } catch (error) {
                    return { success: false, message: 'Error de conexión: ' + error.message };
                }
            }));
            
            const successCount = results.filter(r => r.success).length;
            const failCount = results.length - successCount;
            
            if (successCount > 0) {
                const message = failCount > 0 
                    ? `${successCount} números reservados exitosamente. ${failCount} números no pudieron reservarse.`
                    : `¡${successCount} números reservados exitosamente! Tienes 30 minutos para confirmar el pago. Se abrirá el grupo de WhatsApp.`;
                
                this.showAlert(message, successCount === results.length ? 'success' : 'warning');
                
                // Guardar sesión del usuario con tiempo de expiración
                const phoneNumber = document.getElementById('phoneNumber').value;
                const fullName = document.getElementById('fullName').value;
                const reservationExpires = Date.now() + (30 * 60 * 1000); // 30 minutos desde ahora
                this.saveUserSession(phoneNumber, fullName, this.selectedNumbers, reservationExpires);
                
                // Cerrar modal de registro
                const modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
                if (modal) {
                    modal.hide();
                }
                
                this.startReservationTimer(30 * 60); // 30 minutos
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
            
            // Debug: mostrar errores detallados
            if (failCount > 0) {
                console.error('Errores en registro:', results.filter(r => !r.success));
                
                // Mostrar detalles de errores para debug
                results.filter(r => !r.success).forEach((result, index) => {
                    console.error(`Error ${index + 1}:`, result.message);
                    if (result.debug) {
                        console.error(`Debug info:`, result.debug);
                    }
                });
            }
            
        } catch (error) {
            console.error('❌ Error general en submitRegistration:', error);
            console.error('Stack trace:', error.stack);
            this.showAlert('Error inesperado: ' + error.message, 'danger');
        } finally {
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Participación';
        }
    }
    
    // Timer de reserva (30 minutos)
    startReservationTimer(seconds) {
        this.clearReservationTimer();
        
        // Mostrar la alerta de reserva
        const reservationAlert = document.getElementById('reservationAlert');
        if (reservationAlert) {
            reservationAlert.style.display = 'block';
        }
        
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
                
                // Ocultar alerta de reserva
                const reservationAlert = document.getElementById('reservationAlert');
                if (reservationAlert) {
                    reservationAlert.style.display = 'none';
                }
                
                // Limpiar sesión ya que la reserva expiró
                this.clearUserSession();
                
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
        
        // Ocultar alerta de reserva
        const reservationAlert = document.getElementById('reservationAlert');
        if (reservationAlert) {
            reservationAlert.style.display = 'none';
        }
        
        const timerElement = document.getElementById('reservationTimer');
        if (timerElement) {
            timerElement.textContent = '30:00';
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
        // Crear copos de nieve iniciales (reducido 75% del original)
        for (let i = 0; i < 12; i++) {
            this.createSnowflake();
        }
        
        // Crear nuevos copos periódicamente (aún menos frecuente)
        setInterval(() => {
            this.createSnowflake();
        }, 1200);
        
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
        snowflake.style.animationDuration = (Math.random() * 6 + 5) + 's'; // Más lento: 5-11s en lugar de 3-7s
        snowflake.style.opacity = Math.random() * 0.8 + 0.2;
        snowflake.style.fontSize = (Math.random() * 15 + 15) + 'px';
        
        // Delay aleatorio para que no todos caigan al mismo tiempo
        snowflake.style.animationDelay = Math.random() * 2 + 's';
        
        this.snowContainer.appendChild(snowflake);
        this.snowflakes.push(snowflake);
        
        // Remover el copo después de que termine la animación (ajustado para velocidad más lenta)
        setTimeout(() => {
            if (snowflake.parentNode) {
                snowflake.parentNode.removeChild(snowflake);
            }
        }, 12000); // Aumentado de 8s a 12s para coincidir con la velocidad más lenta
    }
    
    // Limpiar copos de nieve antiguos
    cleanupSnowflakes() {
        this.snowflakes = this.snowflakes.filter(snowflake => {
            if (!snowflake.parentNode) {
                return false;
            }
            return true;
        });
        
        // Mantener un máximo de 25 copos (reducido 75% del original)
        if (this.snowflakes.length > 25) {
            const excess = this.snowflakes.splice(0, this.snowflakes.length - 25);
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
    
    // Funciones para manejar sesión del usuario
    saveUserSession(phoneNumber, fullName, selectedNumbers = [], reservationExpires = null) {
        const sessionData = {
            phoneNumber: phoneNumber,
            fullName: fullName,
            selectedNumbers: selectedNumbers,
            timestamp: Date.now(),
            reservationExpires: reservationExpires // Timestamp de cuando expira la reserva
        };
        localStorage.setItem('sorteo_user_session', JSON.stringify(sessionData));
        console.log('💾 Sesión guardada:', sessionData);
    }
    
    getUserSession() {
        try {
            const sessionData = localStorage.getItem('sorteo_user_session');
            if (sessionData) {
                const parsed = JSON.parse(sessionData);
                // Verificar que la sesión no sea muy antigua (24 horas)
                const maxAge = 24 * 60 * 60 * 1000; // 24 horas en ms
                if (Date.now() - parsed.timestamp < maxAge) {
                    console.log('📱 Sesión recuperada:', parsed);
                    return parsed;
                }
            }
        } catch (error) {
            console.error('❌ Error recuperando sesión:', error);
        }
        return null;
    }
    
    clearUserSession() {
        localStorage.removeItem('sorteo_user_session');
        console.log('🗑️ Sesión limpiada');
    }
    
    // Auto-completar formulario con datos de sesión
    loadUserSessionData() {
        const session = this.getUserSession();
        if (session) {
            const phoneInput = document.getElementById('phoneNumber');
            const nameInput = document.getElementById('fullName');
            const consultaPhoneInput = document.getElementById('consultaPhone');
            
            if (phoneInput && session.phoneNumber) {
                phoneInput.value = session.phoneNumber;
                // Actualizar concepto de pago con el número de la sesión
                this.updatePaymentConcept(session.phoneNumber);
            }
            if (nameInput && session.fullName) {
                nameInput.value = session.fullName;
            }
            if (consultaPhoneInput && session.phoneNumber) {
                consultaPhoneInput.value = session.phoneNumber;
            }
            
            // Auto-consultar boletos si hay número guardado
            if (session.phoneNumber) {
                this.autoConsultTickets(session.phoneNumber);
            }
            
            // Verificar si hay reserva activa y mostrar temporizador
            this.checkActiveReservation(session);
            
            // Como fallback, verificar si hay tiempo de reserva guardado en sesión
            if (session.reservationExpires) {
                const timeLeft = Math.max(0, Math.floor((session.reservationExpires - Date.now()) / 1000));
                if (timeLeft > 0) {
                    console.log(`⏰ Iniciando temporizador desde sesión: ${timeLeft} segundos`);
                    this.startReservationTimer(timeLeft);
                }
            }
        }
    }
    
    // Auto-consultar boletos del usuario
    async autoConsultTickets(phoneNumber) {
        try {
            const response = await fetch('api/consulta_boletos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `phoneNumber=${encodeURIComponent(phoneNumber)}`
            });
            
            const data = await response.json();
            
            if (data.success && data.boletos && data.boletos.length > 0) {
                // Mostrar información de boletos existentes
                this.showExistingTicketsInfo(data.boletos);
            }
        } catch (error) {
            console.error('❌ Error consultando boletos automáticamente:', error);
        }
    }
    
    // Mostrar información de boletos existentes
    showExistingTicketsInfo(boletos) {
        const reservedTickets = boletos.filter(b => b.status === 'reserved');
        const confirmedTickets = boletos.filter(b => b.status === 'confirmed');
        
        if (reservedTickets.length > 0) {
            const numbers = reservedTickets.map(b => b.number_value).join(', ');
            this.showAlert(`Tienes ${reservedTickets.length} boleto(s) reservado(s): ${numbers}. ¡Completa tu pago para confirmarlos!`, 'warning');
            
            // Iniciar temporizador si hay boletos reservados
            this.startReservationTimerFromTickets(reservedTickets);
        }
        
        if (confirmedTickets.length > 0) {
            const numbers = confirmedTickets.map(b => b.number_value).join(', ');
            this.showAlert(`¡Perfecto! Tienes ${confirmedTickets.length} boleto(s) confirmado(s): ${numbers}`, 'success');
        }
    }
    
    // Verificar reserva activa desde sesión
    async checkActiveReservation(session) {
        if (!session || !session.phoneNumber) return;
        
        try {
            const response = await fetch('api/consulta_boletos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `phoneNumber=${encodeURIComponent(session.phoneNumber)}`
            });
            
            const data = await response.json();
            
            if (data.success && data.boletos && data.boletos.length > 0) {
                const reservedTickets = data.boletos.filter(b => b.estado === 'reserved');
                
                if (reservedTickets.length > 0) {
                    console.log('🔍 Reservas activas encontradas:', reservedTickets);
                    this.startReservationTimerFromTickets(reservedTickets);
                }
            }
        } catch (error) {
            console.error('❌ Error verificando reservas activas:', error);
        }
    }
    
    // Iniciar temporizador basado en boletos reservados
    startReservationTimerFromTickets(reservedTickets) {
        if (!reservedTickets || reservedTickets.length === 0) return;
        
        // Buscar el boleto con mayor tiempo restante
        let maxTimeLeft = 0;
        let latestTicket = null;
        
        reservedTickets.forEach(ticket => {
            if (ticket.expira_en) {
                // Parsear tiempo restante (formato: "29:45" o "1:23:45")
                const timeParts = ticket.expira_en.split(':');
                let seconds = 0;
                
                if (timeParts.length === 2) {
                    // Formato MM:SS
                    seconds = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);
                } else if (timeParts.length === 3) {
                    // Formato HH:MM:SS
                    seconds = parseInt(timeParts[0]) * 3600 + parseInt(timeParts[1]) * 60 + parseInt(timeParts[2]);
                }
                
                if (seconds > maxTimeLeft) {
                    maxTimeLeft = seconds;
                    latestTicket = ticket;
                }
            }
        });
        
        if (maxTimeLeft > 0) {
            console.log(`⏰ Iniciando temporizador con ${maxTimeLeft} segundos restantes`);
            this.startReservationTimer(maxTimeLeft);
        }
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

// Inicializar el manager del sorteo
let sorteoManager;

function initializeSorteoManager() {
    if (!sorteoManager) {
        console.log('🎄 Inicializando Sorteo Manager...');
        sorteoManager = new SorteoApp();
        console.log('✅ Sorteo Manager inicializado correctamente');
    }
    return sorteoManager;
}

// Función global para el botón
function submitRegistration() {
    console.log('🚀 submitRegistration() llamada desde botón');
    console.log('🔍 Estado actual:', {
        sorteoManager: !!sorteoManager,
        hasSubmitMethod: !!(sorteoManager && sorteoManager.submitRegistration),
        readyState: document.readyState
    });
    
    const manager = initializeSorteoManager();
    if (manager && manager.submitRegistration) {
        manager.submitRegistration();
    } else {
        console.error('❌ Error: sorteoManager o submitRegistration no disponible');
        console.error('Manager:', manager);
        console.error('SubmitRegistration method:', manager ? manager.submitRegistration : 'manager is null');
    }
}

// Función global para abrir WhatsApp
function openWhatsAppGroup() {
    console.log('🚀 openWhatsAppGroup() llamada desde botón');
    const manager = initializeSorteoManager();
    if (manager && manager.openWhatsAppGroup) {
        manager.openWhatsAppGroup();
    } else {
        // Fallback directo si hay problemas con el manager
        const whatsappUrl = 'https://chat.whatsapp.com/JFCyYfRXYJVJkiLsVj2yhK?mode=wwt';
        window.open(whatsappUrl, '_blank');
        console.log('📱 WhatsApp abierto directamente');
    }
}

// Función de debug global
function debugSorteoManager() {
    console.log('🔍 Debug Sorteo Manager:', {
        sorteoManager: sorteoManager,
        isInstance: sorteoManager instanceof SorteoApp,
        methods: sorteoManager ? Object.getOwnPropertyNames(Object.getPrototypeOf(sorteoManager)) : 'No manager',
        readyState: document.readyState
    });
}

// Inicializar cuando DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSorteoManager);
} else {
    // DOM ya está listo
    initializeSorteoManager();
}
