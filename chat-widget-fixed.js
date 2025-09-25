/**
 * Widget de Chat Automatizado para Mizton Landing - VERSIÓN CORREGIDA
 * Se activa solo cuando landing_preference = 0
 */

class MiztonChatWidget {
    constructor() {
        this.chatAPI = './api/chat-handler.php';
        this.sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        this.userEmail = null;
        this.currentStep = 'email_capture'; // email_capture, referral_code_capture, referral_confirmation, chatting
        this.referralCode = null;
        this.referrerData = null;
        this.pendingReferralCode = null;
        this.pendingReferrerName = null;
        this.isActive = false;
        
        this.init();
    }

    // Método para abrir el chat
    open() {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.style.display = 'flex';
            
            // Focus en el input después de un momento
            setTimeout(() => {
                const chatInput = document.getElementById('chat-input');
                if (chatInput) {
                    chatInput.focus();
                }
            }, 300);
        }
    }

    init() {
        this.checkIfShouldActivate();
    }

    getReferralFromURL() {
        const params = new URLSearchParams(window.location.search);
        return params.get('ref') || null;
    }

    async checkIfShouldActivate() {
        try {
            this.referralCode = this.getReferralFromURL();
            
            const response = await fetch('./api/referral-info.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ref: this.referralCode })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.referrerData = data.data;
                this.activate();
                console.log('🤖 Chat automatizado activado');
            } else {
                this.activate();
                console.log('🤖 Chat activado por defecto');
            }
        } catch (error) {
            console.error('Error verificando referido:', error);
            this.activate();
        }
    }

    activate() {
        this.isActive = true;
        this.createWidget();
        console.log('🤖 Chat automatizado activado');
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.innerHTML = `
            <!-- Chat Container -->
            <div id="chat-container" style="
                position: fixed;
                bottom: 90px;
                right: 20px;
                width: 350px;
                height: 500px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                display: none;
                flex-direction: column;
                z-index: 1000;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            ">
                <!-- Header -->
                <div id="chat-header" style="
                    background: linear-gradient(135deg, #1B4332 0%, #2D5A3D 100%);
                    color: white;
                    padding: 15px;
                    border-radius: 15px 15px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    box-shadow: 0 4px 12px rgba(27, 67, 50, 0.15);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="
                            width: 40px;
                            height: 40px;
                            background: rgba(116, 198, 157, 0.2);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 20px;
                            border: 2px solid rgba(116, 198, 157, 0.3);">🌱</div>
                        <div>
                            <div style="font-weight: 600; font-size: 16px;">Asistente Mizton</div>
                            <div style="font-size: 12px; opacity: 0.9; color: #95D5B2;">En línea</div>
                        </div>
                    </div>
                    <button id="close-chat" style="
                        background: none;
                        border: none;
                        color: white;
                        font-size: 20px;
                        cursor: pointer;
                        padding: 5px;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.3s ease;" onmouseover="this.style.background='rgba(116, 198, 157, 0.2)'" onmouseout="this.style.background='none'">×</button>
                </div>

                <!-- Messages -->
                <div id="chat-messages" style="
                    flex: 1;
                    padding: 15px;
                    overflow-y: auto;
                    background: #F8F9FA;
                ">
                    <div class="bot-message" style="
                        background: white;
                        padding: 12px 16px;
                        border-radius: 18px 18px 18px 4px;
                        margin-bottom: 12px;
                        box-shadow: 0 2px 4px rgba(27, 67, 50, 0.1);
                        border-left: 3px solid #40916C;
                        max-width: 85%;
                        color: #343A40;
                    ">
                        ¡Hola! 👋 Soy el asistente virtual de Mizton. Para comenzar, ¿podrías compartir tu email?
                    </div>
                </div>

                <!-- Escalation Button (Initially Hidden) -->
                <div id="escalation-container" style="
                    padding: 10px 15px;
                    text-align: center;
                    border-top: 1px solid #E9ECEF;
                    background: #F8F9FA;
                    display: none;
                ">
                    <button 
                        id="escalate-button-permanent"
                        style="
                            background: linear-gradient(135deg, #52B788 0%, #74C69D 100%);
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 20px;
                            font-size: 12px;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            box-shadow: 0 2px 6px rgba(82, 183, 136, 0.3);
                            font-weight: 500;"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(82, 183, 136, 0.4)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(82, 183, 136, 0.3)'"
                    >
                        👤 ¿Necesitas hablar con un asesor?
                    </button>
                </div>

                <!-- Suggestion Buttons -->
                <div id="suggestion-buttons" style="
                    padding: 15px;
                    border-top: 1px solid #E9ECEF;
                    background: #F8F9FA;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    justify-content: center;
                ">
                    <button class="suggestion-btn" data-query="que es mizton" style="
                        background: rgba(64, 145, 108, 0.1);
                        color: #1B4332;
                        border: 1px solid rgba(64, 145, 108, 0.3);
                        padding: 6px 12px;
                        border-radius: 15px;
                        font-size: 11px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">¿Qué es Mizton?</button>
                    
                    <button class="suggestion-btn" data-query="como funciona" style="
                        background: rgba(64, 145, 108, 0.1);
                        color: #1B4332;
                        border: 1px solid rgba(64, 145, 108, 0.3);
                        padding: 6px 12px;
                        border-radius: 15px;
                        font-size: 11px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">¿Cómo funciona?</button>
                    
                    <button class="suggestion-btn" data-query="membresia" style="
                        background: rgba(64, 145, 108, 0.1);
                        color: #1B4332;
                        border: 1px solid rgba(64, 145, 108, 0.3);
                        padding: 6px 12px;
                        border-radius: 15px;
                        font-size: 11px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">Membresía</button>
                    
                    <button class="suggestion-btn" data-query="cuanto puedo ganar" style="
                        background: rgba(64, 145, 108, 0.1);
                        color: #1B4332;
                        border: 1px solid rgba(64, 145, 108, 0.3);
                        padding: 6px 12px;
                        border-radius: 15px;
                        font-size: 11px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        font-weight: 500;
                    ">¿Cuánto se gana?</button>
                </div>

                <!-- Input -->
                <div id="chat-input-container" style="
                    border-top: 1px solid #E9ECEF;
                    padding: 15px;
                    display: flex;
                    gap: 10px;
                    background: white;
                ">
                    <input 
                        type="text" 
                        id="chat-input" 
                        placeholder="Escribe tu mensaje..."
                        style="
                            flex: 1;
                            padding: 12px 16px;
                            border: 1px solid #DEE2E6;
                            border-radius: 25px;
                            outline: none;
                            font-size: 14px;
                            transition: border-color 0.3s ease;
                            color: #343A40;
                        "
                        onkeypress="if(event.key==='Enter') document.getElementById('send-message').click()"
                        onfocus="this.style.borderColor='#40916C'"
                        onblur="this.style.borderColor='#DEE2E6'"
                    />
                    <button 
                        id="send-message" 
                        style="
                            background: linear-gradient(135deg, #40916C 0%, #52B788 100%);
                            color: white;
                            border: none;
                            border-radius: 50%;
                            width: 45px;
                            height: 45px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 18px;
                            transition: all 0.3s ease;
                            box-shadow: 0 2px 8px rgba(64, 145, 108, 0.3);"
                        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(64, 145, 108, 0.4)'"
                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 2px 8px rgba(64, 145, 108, 0.3)'"
                    >➤</button>
                </div>
            </div>

            <!-- Toggle Button -->
            <button id="chat-toggle" style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #1B4332 0%, #2D5A3D 100%);
                color: white;
                border: none;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(27, 67, 50, 0.25);
                z-index: 1001;
                transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">🌱</button>
        `;

        document.body.appendChild(widget);
        this.bindEvents();
    }

    bindEvents() {
        const toggle = document.getElementById('chat-toggle');
        const container = document.getElementById('chat-container');
        const closeBtn = document.getElementById('close-chat');
        const sendBtn = document.getElementById('send-message');
        const input = document.getElementById('chat-input');

        toggle.addEventListener('click', () => {
            console.log('🔘 Botón flotante clickeado');
            const isVisible = container.style.display === 'flex';
            container.style.display = isVisible ? 'none' : 'flex';
            toggle.style.display = isVisible ? 'block' : 'none';
            
            if (!isVisible) {
                setTimeout(() => {
                    const chatInput = document.getElementById('chat-input');
                    if (chatInput) {
                        chatInput.focus();
                    }
                }, 300);
            }
        });

        closeBtn.addEventListener('click', () => {
            container.style.display = 'none';
            toggle.style.display = 'block';
        });

        sendBtn.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
        
        // Event listener para botón de escalamiento (SOLO UNA VEZ)
        const escalatePermanentBtn = document.getElementById('escalate-button-permanent');
        if (escalatePermanentBtn) {
            escalatePermanentBtn.addEventListener('click', () => {
                console.log('👤 Escalamiento iniciado...');
                this.escalateToHuman();
            });
        }

        // Event listeners para botones de sugerencias
        const suggestionButtons = document.querySelectorAll('.suggestion-btn');
        suggestionButtons.forEach(button => {
            button.addEventListener('click', () => {
                const query = button.getAttribute('data-query');
                const input = document.getElementById('chat-input');
                input.value = query;
                this.sendMessage();
            });

            // Efectos hover
            button.addEventListener('mouseenter', () => {
                button.style.background = 'rgba(64, 145, 108, 0.2)';
                button.style.transform = 'translateY(-1px)';
            });

            button.addEventListener('mouseleave', () => {
                button.style.background = 'rgba(64, 145, 108, 0.1)';
                button.style.transform = 'translateY(0)';
            });
        });
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;

        this.addMessage('user', message);
        input.value = '';
        
        this.showTypingIndicator();

        console.log('🔍 Current step:', this.currentStep, 'Message:', message);
        
        if (this.currentStep === 'email_capture') {
            console.log('📧 Procesando captura de email');
            await this.handleEmailCapture(message);
        } else if (this.currentStep === 'referral_code_capture') {
            console.log('🔗 Procesando captura de código de referido');
            await this.handleReferralCodeCapture(message);
        } else if (this.currentStep === 'referral_confirmation') {
            console.log('✅ Procesando confirmación de referidor');
            await this.handleReferralConfirmation(message);
        } else {
            console.log('💬 Procesando mensaje de chat');
            await this.handleChatMessage(message);
        }
    }

    async handleEmailCapture(message) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailRegex.test(message)) {
            this.userEmail = message;
            
            try {
                const response = await fetch(this.chatAPI, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save_lead',
                        email: message,
                        referral_code: this.referralCode,
                        referrer_id: this.referrerData?.referrer_id,
                        session_id: this.sessionId
                    })
                });

                const data = await response.json();
                
                this.hideTypingIndicator();
                
                if (data.success) {
                    // Si no hay código de referido, preguntar por él
                    if (!this.referralCode) {
                        this.currentStep = 'referral_code_capture';
                        this.addMessage('bot', '¡Perfecto! Una pregunta más: ¿conoces el código de la persona que te invitó a Mizton? Si es así, por favor compártelo (son 6 caracteres alfanuméricos). Si no lo conoces, simplemente escribe "no".');
                    } else {
                        this.currentStep = 'chatting';
                        if (data.data.conversation_history && data.data.conversation_history.length > 0) {
                            this.loadConversationHistory(data.data.conversation_history);
                            this.addMessage('bot', '¡Bienvenido de vuelta! Continuemos donde lo dejamos.');
                        } else {
                            this.addMessage('bot', '¡Perfecto! Ahora puedo ayudarte con cualquier pregunta sobre Mizton. ¿Qué te gustaría saber?');
                            this.hideSuggestionButtons();
                        }
                    }
                } else {
                    this.addMessage('bot', 'Hubo un problema guardando tu información. ¿Podrías intentar de nuevo?');
                }
            } catch (error) {
                console.error('Error guardando lead:', error);
                this.hideTypingIndicator();
                this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar más tarde?');
            }
        } else {
            this.hideTypingIndicator();
            this.addMessage('bot', 'Por favor ingresa un email válido (ejemplo: tu@email.com) para continuar.');
        }
    }

    async handleReferralCodeCapture(message) {
        const cleanMessage = message.toLowerCase().trim();
        
        if (cleanMessage === 'no' || cleanMessage === 'no lo conozco' || cleanMessage === 'no tengo') {
            this.hideTypingIndicator();
            this.currentStep = 'chatting';
            this.addMessage('bot', '¡No hay problema! Ahora puedo ayudarte con cualquier pregunta sobre Mizton. ¿Qué te gustaría saber?');
            this.hideSuggestionButtons();
            return;
        }

        // Verificar que sea un código de 6 caracteres alfanuméricos
        const codeRegex = /^[a-zA-Z0-9]{6}$/;
        if (!codeRegex.test(message)) {
            this.hideTypingIndicator();
            this.addMessage('bot', 'El código debe tener exactamente 6 caracteres alfanuméricos. Por favor intenta de nuevo o escribe "no" si no lo conoces.');
            return;
        }

        try {
            // Verificar el código en el backend
            const response = await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'verify_referral_code',
                    referral_code: message,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            this.hideTypingIndicator();

            if (data.success && data.data.referrer_name) {
                this.pendingReferralCode = message;
                this.pendingReferrerName = data.data.referrer_name;
                this.currentStep = 'referral_confirmation';
                this.addMessage('bot', `¿La persona que te invitó fue ${data.data.referrer_name}? Responde "sí" o "no".`);
            } else {
                this.addMessage('bot', 'No encontré ese código en nuestro sistema. ¿Podrías verificarlo e intentar de nuevo? O escribe "no" si prefieres continuar sin código.');
            }
        } catch (error) {
            console.error('Error verificando código:', error);
            this.hideTypingIndicator();
            this.addMessage('bot', 'Hubo un problema verificando el código. ¿Podrías intentar de nuevo?');
        }
    }

    async handleReferralConfirmation(message) {
        const cleanMessage = message.toLowerCase().trim();
        
        if (cleanMessage === 'sí' || cleanMessage === 'si' || cleanMessage === 'yes' || cleanMessage === 'correcto') {
            try {
                // Actualizar el lead con el código de referido
                const response = await fetch(this.chatAPI, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_referral_code',
                        session_id: this.sessionId,
                        referral_code: this.pendingReferralCode
                    })
                });

                const data = await response.json();
                this.hideTypingIndicator();

                if (data.success) {
                    this.referralCode = this.pendingReferralCode;
                    this.currentStep = 'chatting';
                    this.addMessage('bot', `¡Perfecto! He registrado que ${this.pendingReferrerName} te invitó. Ahora puedo ayudarte con cualquier pregunta sobre Mizton. ¿Qué te gustaría saber?`);
                    this.hideSuggestionButtons();
                } else {
                    this.addMessage('bot', 'Hubo un problema actualizando la información. Pero no te preocupes, puedo ayudarte con cualquier pregunta sobre Mizton.');
                    this.currentStep = 'chatting';
                    this.hideSuggestionButtons();
                }
            } catch (error) {
                console.error('Error actualizando referido:', error);
                this.hideTypingIndicator();
                this.addMessage('bot', 'Hubo un problema técnico, pero puedo ayudarte con cualquier pregunta sobre Mizton.');
                this.currentStep = 'chatting';
                this.hideSuggestionButtons();
            }
        } else {
            this.hideTypingIndicator();
            this.currentStep = 'chatting';
            this.addMessage('bot', '¡No hay problema! Ahora puedo ayudarte con cualquier pregunta sobre Mizton. ¿Qué te gustaría saber?');
            this.hideSuggestionButtons();
        }
    }

    async handleChatMessage(message) {
        try {
            const response = await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_ai_response',
                    message: message,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.hideTypingIndicator();
                this.addMessage('bot', data.data.response);
            } else {
                this.hideTypingIndicator();
                this.addMessage('bot', 'Lo siento, no pude procesar tu mensaje. ¿Podrías reformularlo?');
            }
            
        } catch (error) {
            console.error('Error procesando mensaje:', error);
            this.hideTypingIndicator();
            
            // FAQ básicas como fallback
            const basicFAQs = {
                'hola': '¡Hola! 👋 Soy el asistente de Mizton. ¿En qué puedo ayudarte?',
                'mizton': 'Mizton es una plataforma de membresías garantizadas. ¿Te gustaría saber más?',
                'como funciona': 'Te explico: 1) Te registras, 2) Adquieres membresía, 3) Generas ganancias, 4) Recuperas 100% + 15% mínimo.',
                'precio': 'Desde $50 USD ya participas en los dividendos globales de Mizton.',
                'seguro': 'Totalmente seguro. Garantizamos 100% de recuperación más ganancias mínimas del 15%.'
            };
            
            const userMessage = message.toLowerCase();
            let fallbackResponse = null;
            
            for (const [key, response] of Object.entries(basicFAQs)) {
                if (userMessage.includes(key)) {
                    fallbackResponse = response;
                    break;
                }
            }
            
            if (fallbackResponse) {
                this.addMessage('bot', fallbackResponse);
            } else {
                this.addMessage('bot', 'Disculpa el inconveniente técnico. ¿Podrías reformular tu pregunta? Puedo ayudarte con información sobre Mizton, precios, funcionamiento o seguridad.');
            }
        }
    }

    async escalateToHuman() {
        try {
            const response = await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'escalate_to_human',
                    session_id: this.sessionId,
                    email: this.userEmail,
                    referral_code: this.referralCode
                })
            });

            const data = await response.json();
            
            if (data.success && data.data) {
                const contactInfo = data.data;
                const basicMessage = `Hola! Vengo de la landing page de Mizton y me gustaría obtener más información. Mi email es: ${this.userEmail}`;
                
                let escalationMessage = '';
                
                if (contactInfo.contact_method === 'whatsapp_personal') {
                    escalationMessage = `${contactInfo.message} 
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent(basicMessage)}" target="_blank" style="color: #40916C; text-decoration: underline; font-weight: bold;">
                    👤 Contactar a ${contactInfo.referrer_name || 'tu asesor'} por WhatsApp
                    </a>`;
                } else {
                    escalationMessage = `${contactInfo.message}
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent(basicMessage)}" target="_blank" style="color: #40916C; text-decoration: underline; font-weight: bold;">
                    📱 Contactar equipo de asesores por WhatsApp
                    </a>`;
                }
                
                this.addMessage('bot', escalationMessage, true);
                
            } else {
                this.addMessage('bot', 'Disculpa, hubo un problema al conectarte con un asesor. ¿Podrías intentar más tarde?');
            }
        } catch (error) {
            console.error('Error escalando:', error);
            this.addMessage('bot', 'Disculpa, hubo un problema al conectarte con un asesor. ¿Podrías intentar más tarde?');
        }
    }

    addMessage(sender, message, allowHTML = false) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        
        if (sender === 'user') {
            messageDiv.style.cssText = `
                background: linear-gradient(135deg, #40916C 0%, #52B788 100%);
                color: white;
                padding: 12px 16px;
                border-radius: 18px 18px 4px 18px;
                margin-bottom: 12px;
                margin-left: auto;
                max-width: 85%;
                word-wrap: break-word;
                box-shadow: 0 2px 4px rgba(64, 145, 108, 0.2);
            `;
        } else {
            messageDiv.className = 'bot-message';
            messageDiv.style.cssText = `
                background: white;
                padding: 12px 16px;
                border-radius: 18px 18px 18px 4px;
                margin-bottom: 12px;
                box-shadow: 0 2px 4px rgba(27, 67, 50, 0.1);
                border-left: 3px solid #40916C;
                max-width: 85%;
                color: #343A40;
                word-wrap: break-word;
            `;
        }
        
        if (allowHTML) {
            messageDiv.innerHTML = message;
        } else {
            messageDiv.textContent = message;
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.style.cssText = `
            background: white;
            padding: 12px 16px;
            border-radius: 18px 18px 18px 4px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(27, 67, 50, 0.1);
            border-left: 3px solid #40916C;
            max-width: 85%;
            color: #343A40;
        `;
        
        typingDiv.innerHTML = `
            <div style="display: flex; gap: 4px; align-items: center;">
                <span>Escribiendo</span>
                <div style="display: flex; gap: 2px;">
                    <div style="width: 4px; height: 4px; background: #52B788; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both;"></div>
                    <div style="width: 4px; height: 4px; background: #74C69D; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: -0.32s;"></div>
                    <div style="width: 4px; height: 4px; background: #95D5B2; border-radius: 50%; animation: bounce 1.4s infinite ease-in-out both; animation-delay: -0.16s;"></div>
                </div>
            </div>
            <style>
                @keyframes bounce {
                    0%, 80%, 100% { transform: scale(0); }
                    40% { transform: scale(1); }
                }
            </style>
        `;
        
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    loadConversationHistory(conversationHistory) {
        // Implementar si es necesario
        console.log('Cargando historial:', conversationHistory);
    }

    hideSuggestionButtons() {
        const suggestionButtons = document.getElementById('suggestion-buttons');
        if (suggestionButtons) {
            suggestionButtons.style.display = 'none';
        }
    }

    showEscalationButton() {
        const escalationContainer = document.getElementById('escalation-container');
        if (escalationContainer) {
            escalationContainer.style.display = 'block';
        }
    }
}

// Auto-inicializar cuando se carga el DOM
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.miztonChatInstance === 'undefined') {
        window.miztonChatInstance = new MiztonChatWidget();
    }
});
