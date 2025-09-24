/**
 * Widget de Chat Automatizado para Mizton Landing
 * Se activa solo cuando landing_preference = 0
 */

class MiztonChatWidget {
    constructor() {
        this.isActive = false;
        this.sessionId = this.generateSessionId();
        this.referralCode = this.getReferralFromURL();
        this.chatAPI = '/api/chat-handler.php';
        this.currentStep = 'initial'; // initial, waiting_email, chatting
        this.userEmail = null;
        this.referrerData = null;
    }

    getReferralFromURL() {
        const params = new URLSearchParams(window.location.search);
        return params.get('ref') || null;
    }

    generateSessionId() {
        return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    async checkIfShouldActivate(referralCode) {
        try {
            const response = await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'validate_referral',
                    referral_code: referralCode
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // NUEVO ENFOQUE: Siempre activar chat
                this.referrerData = data.data;
                this.activate();
                console.log('🤖 Chat automatizado activado para todos los usuarios');
                
                if (data.data.valid && !data.data.use_chat) {
                    console.log('📱 Referidor tiene atención personal - escalamiento dirigido');
                }
            } else {
                // En caso de error, activar chat por defecto
                this.activate();
                console.log('🤖 Chat activado por defecto');
            }
        } catch (error) {
            console.error('Error verificando referido:', error);
            // En caso de error, activar chat por defecto
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
        widget.id = 'mizton-chat-widget';
        widget.innerHTML = `
            <div id="chat-container" style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 350px;
                height: 500px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 30px rgba(0,0,0,0.2);
                display: none;
                flex-direction: column;
                z-index: 1000;
            ">
                <div id="chat-header" style="
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px;
                    border-radius: 15px 15px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div>
                        <h4 style="margin: 0; font-size: 16px;">Asistente Mizton 🤖</h4>
                        <small>Estamos aquí para ayudarte</small>
                    </div>
                    <button id="close-chat" style="
                        background: none;
                        border: none;
                        color: white;
                        font-size: 20px;
                        cursor: pointer;
                    ">×</button>
                </div>
                
                <div id="chat-messages" style="
                    flex: 1;
                    padding: 15px;
                    overflow-y: auto;
                    background: #f8f9fa;
                ">
                    <div class="bot-message" style="
                        background: white;
                        border: 1px solid #eee;
                        padding: 10px;
                        border-radius: 10px;
                        margin-bottom: 10px;
                    ">
                        ¡Hola! 👋 Soy el asistente virtual de Mizton. 
                        ${this.referrerData?.referrer_name ? 
                            `Veo que vienes por recomendación de <strong>${this.referrerData.referrer_name}</strong>. ` : 
                            this.referralCode ? 
                                'Veo que tienes un código de referido. ' :
                                '¿Conoces el código de referido de quien te invitó? Si no, no te preocupes. '
                        }
                        <br><br>
                        Puedo ayudarte con información sobre Mizton, responder tus preguntas y ${this.referrerData?.referrer_name ? 
                            `conectarte directamente con <strong>${this.referrerData.referrer_name}</strong>` : 
                            'conectarte con nuestros asesores especializados'
                        } cuando lo necesites.
                        <br><br>
                        Para comenzar, ¿podrías compartir tu email?
                    </div>
                </div>
                
                <div id="chat-input-container" style="
                    border-top: 1px solid #eee;
                    display: flex;
                    gap: 10px;
                ">
                    <input 
                        type="text" 
                        id="chat-input" 
                        placeholder="Escribe tu mensaje..."
                        style="
                            flex: 1;
                            padding: 10px;
                            border: 1px solid #ddd;
                            border-radius: 20px;
                            outline: none;
                        "
                    />
                    <button 
                        id="send-message"
                        style="
                            padding: 10px 15px;
                            background: #667eea;
                            color: white;
                            border: none;
                            border-radius: 50%;
                            cursor: pointer;
                        "
                    >
                        ➤
                    </button>
                </div>
                
                <div style="
                    padding: 10px 15px;
                    text-align: center;
                    border-top: 1px solid #eee;
                    background: #f9f9f9;
                ">
                    <button 
                        id="escalate-button-permanent"
                        style="
                            background: transparent;
                            border: 1px solid #667eea;
                            color: #667eea;
                            padding: 8px 16px;
                            border-radius: 20px;
                            cursor: pointer;
                            font-size: 12px;
                            transition: all 0.3s ease;
                        "
                    >
                        👤 ¿Necesitas hablar con un asesor?
                    </button>
                </div>
            </div>
            
            <button id="chat-toggle" style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 3px 15px rgba(0,0,0,0.2);
                z-index: 1001;
            ">💬</button>
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
            const isVisible = container.style.display === 'flex';
            container.style.display = isVisible ? 'none' : 'flex';
            toggle.style.display = isVisible ? 'block' : 'none';
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
        
        // Event listener para botón permanente de escalamiento
        const escalatePermanentBtn = document.getElementById('escalate-button-permanent');
        if (escalatePermanentBtn) {
            escalatePermanentBtn.addEventListener('click', () => {
                this.escalateToHuman();
            });
            
            // Hover effects
            escalatePermanentBtn.addEventListener('mouseenter', () => {
                escalatePermanentBtn.style.background = '#667eea';
                escalatePermanentBtn.style.color = 'white';
            });
            
            escalatePermanentBtn.addEventListener('mouseleave', () => {
                escalatePermanentBtn.style.background = 'transparent';
                escalatePermanentBtn.style.color = '#667eea';
            });
        }
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;

        this.addMessage('user', message);
        input.value = '';
        
        // Mostrar spinner de "escribiendo..."
        this.showTypingIndicator();

        // Procesar mensaje según el paso actual
        if (this.currentStep === 'initial' || this.currentStep === 'waiting_email') {
            await this.handleEmailCapture(message);
        } else {
            await this.handleChatMessage(message);
        }
    }

    async handleEmailCapture(message) {
        // Verificar si es un email válido
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (emailRegex.test(message)) {
            this.userEmail = message;
            
            try {
                // Guardar lead en la base de datos
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
                
                if (data.success) {
                    this.userEmail = message;
                    this.currentStep = 'chatting';
                    
                    if (data.existing_user && data.data.conversation_history) {
                        // Usuario existente - cargar historial
                        this.loadConversationHistory(data.data.conversation_history);
                        this.addMessage('bot', data.message); // "Bienvenido de vuelta! Continuemos donde lo dejamos."
                    } else {
                        // Usuario nuevo
                        this.addMessage('bot', data.message); // "Perfecto! Ahora puedo ayudarte..."
                    }
                } else {
                    this.addMessage('bot', 'Hubo un problema guardando tu información. ¿Podrías intentar de nuevo?');
                }
            } catch (error) {
                console.error('Error guardando lead:', error);
                this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar más tarde?');
            }
        } else {
            this.addMessage('bot', 'Por favor ingresa un email válido (ejemplo: tu@email.com) para continuar.');
        }
    }

    loadConversationHistory(conversationHistory) {
        // Limpiar mensajes actuales excepto el inicial
        const messages = document.getElementById('chat-messages');
        const initialMessage = messages.querySelector('.bot-message');
        messages.innerHTML = '';
        
        // Restaurar mensaje inicial
        if (initialMessage) {
            messages.appendChild(initialMessage);
        }
        
        // Cargar historial
        conversationHistory.forEach(msg => {
            this.addMessage(msg.sender, msg.message, false, false); // Sin scroll automático
        });
        
        // Scroll al final después de cargar todo
        setTimeout(() => {
            messages.scrollTop = messages.scrollHeight;
        }, 100);
        
        console.log('📜 Historial de conversación cargado:', conversationHistory.length, 'mensajes');
    }

    showTypingIndicator() {
        const messages = document.getElementById('chat-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'bot-message';
        typingDiv.style.cssText = `
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            background: white;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
        `;
        
        typingDiv.innerHTML = `
            <div style="display: flex; gap: 4px;">
                <div class="typing-dot" style="
                    width: 8px;
                    height: 8px;
                    background: #667eea;
                    border-radius: 50%;
                    animation: typing 1.4s infinite ease-in-out;
                "></div>
                <div class="typing-dot" style="
                    width: 8px;
                    height: 8px;
                    background: #667eea;
                    border-radius: 50%;
                    animation: typing 1.4s infinite ease-in-out 0.2s;
                "></div>
                <div class="typing-dot" style="
                    width: 8px;
                    height: 8px;
                    background: #667eea;
                    border-radius: 50%;
                    animation: typing 1.4s infinite ease-in-out 0.4s;
                "></div>
            </div>
            <span style="color: #888; font-style: italic;">Escribiendo...</span>
        `;
        
        // Agregar CSS para animación
        if (!document.getElementById('typing-animation-css')) {
            const style = document.createElement('style');
            style.id = 'typing-animation-css';
            style.textContent = `
                @keyframes typing {
                    0%, 60%, 100% {
                        transform: translateY(0);
                        opacity: 0.4;
                    }
                    30% {
                        transform: translateY(-10px);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        messages.appendChild(typingDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    async handleChatMessage(message) {
        try {
            // Actualizar conversación
            await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_conversation',
                    session_id: this.sessionId,
                    message: message,
                    sender: 'user'
                })
            });

            // Obtener respuesta (FAQ o IA según configuración)
            const response = await fetch(this.chatAPI, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_ai_response', // Intentará IA primero, fallback a FAQ
                    message: message,
                    session_id: this.sessionId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Determinar si es respuesta FAQ o IA
                const isFAQResponse = !data.data.powered_by || data.data.powered_by === 'faq';
                
                if (isFAQResponse) {
                    // Para FAQ: delay de 3 segundos para simular escritura humana
                    setTimeout(() => {
                        this.hideTypingIndicator();
                        this.addMessage('bot', data.data.response);
                        
                        // Verificar escalamiento después del mensaje
                        if (data.data.requires_human) {
                            setTimeout(() => {
                                this.showEscalationButton();
                            }, 1000);
                        }
                    }, 3000);
                } else {
                    // Para IA: respuesta inmediata (la IA ya tiene su propio delay)
                    this.hideTypingIndicator();
                    this.addMessage('bot', data.data.response);
                    
                    if (data.data.requires_human) {
                        setTimeout(() => {
                            this.showEscalationButton();
                        }, 1000);
                    }
                }
                
                // Actualizar conversación con respuesta del bot
                await fetch(this.chatAPI, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_conversation',
                        session_id: this.sessionId,
                        message: data.data.response,
                        sender: 'bot'
                    })
                });
            } else {
                this.hideTypingIndicator();
                this.addMessage('bot', 'Lo siento, no pude procesar tu mensaje. ¿Podrías reformularlo?');
            }
            
        } catch (error) {
            console.error('Error procesando mensaje:', error);
            this.hideTypingIndicator();
            this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar más tarde?');
        }
    }

    showEscalationButton() {
        // Verificar si el botón ya existe
        if (document.getElementById('escalation-button')) {
            return;
        }
        
        const chatContainer = document.getElementById('chat-widget');
        if (!chatContainer) return;
        
        // Crear botón de escalamiento
        const escalationButton = document.createElement('div');
        escalationButton.id = 'escalation-button';
        escalationButton.style.cssText = `
            margin: 10px;
            text-align: center;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        `;
        
        escalationButton.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span>👤</span>
                <span>Hablar con asesor especializado</span>
                <span>📱</span>
            </div>
        `;
        
        // Agregar animación CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
                50% { box-shadow: 0 4px 25px rgba(102, 126, 234, 0.6); }
                100% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
            }
        `;
        document.head.appendChild(style);
        
        // Evento click
        escalationButton.addEventListener('click', () => {
            this.escalateToHuman();
        });
        
        // Hover effects
        escalationButton.addEventListener('mouseenter', () => {
            escalationButton.style.transform = 'scale(1.05)';
        });
        
        escalationButton.addEventListener('mouseleave', () => {
            escalationButton.style.transform = 'scale(1)';
        });
        
        // Insertar antes del input container
        const inputContainer = document.getElementById('chat-input-container');
        if (inputContainer) {
            chatContainer.insertBefore(escalationButton, inputContainer);
        } else {
            chatContainer.appendChild(escalationButton);
        }
        
        console.log('👤 Botón de escalamiento mostrado');
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
                // Generar mensaje con contexto
                const contextMessage = await this.generateContextMessage(contactInfo);
                let escalationMessage = '';
                
                if (contactInfo.contact_method === 'whatsapp_personal') {
                    escalationMessage = `${contactInfo.message} 
                    
                    📋 <strong>Se enviará un resumen de nuestra conversación para que ${contactInfo.referrer_name} tenga el contexto completo.</strong>
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent(contextMessage)}" target="_blank" style="color: #667eea; text-decoration: underline;">
                    👤 Contactar a ${contactInfo.referrer_name || 'tu asesor'} por WhatsApp
                    </a>`;
                } else {
                    escalationMessage = `${contactInfo.message}
                    
                    📋 <strong>Se enviará un resumen de nuestra conversación al asesor.</strong>
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent(contextMessage)}" target="_blank" style="color: #667eea; text-decoration: underline;">
                    📱 Contactar equipo de asesores por WhatsApp
                    </a>`;
                }
                
                this.addMessage('bot', escalationMessage, true); // true para permitir HTML
                
                // Deshabilitar input después del escalamiento
                this.disableChatInput();
                
                // Mostrar botones de WhatsApp en la landing
                this.showWhatsAppButtons(contactInfo);
                
            } else {
                this.addMessage('bot', 'Disculpa, hubo un problema al conectarte con un asesor. ¿Podrías intentar más tarde?');
            }
            
        } catch (error) {
            console.error('Error escalando a humano:', error);
            this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar contactarnos directamente por WhatsApp?');
        }
    }

    async generateContextMessage(contactInfo) {
        try {
            const response = await fetch('/api/context-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get_conversation_context',
                    session_id: this.sessionId,
                    referrer_name: contactInfo.referrer_name
                })
            });

            const data = await response.json();
            
            if (data.success) {
                return data.data.whatsapp_message;
            } else {
                // Mensaje fallback
                return `Hola! Soy ${this.userEmail || 'un prospecto'} y vengo del chat de la landing page de Mizton. Me gustaría hablar contigo sobre las membresías. ¡Gracias!`;
            }
            
        } catch (error) {
            console.error('Error generando contexto:', error);
            // Mensaje fallback
            return `Hola! Soy ${this.userEmail || 'un prospecto'} y vengo del chat de la landing page de Mizton. Me gustaría hablar contigo sobre las membresías. ¡Gracias!`;
        }
    }

    showWhatsAppButtons(contactInfo) {
        // Llamar función global para mostrar botones de WhatsApp
        if (typeof showWhatsAppButtonsAfterEscalation === 'function') {
            const referrerName = contactInfo.referrer_name || null;
            showWhatsAppButtonsAfterEscalation(contactInfo.contact_info, referrerName);
        }
        
        // Remover botón de escalamiento
        const escalationButton = document.getElementById('escalation-button');
        if (escalationButton) {
            escalationButton.remove();
        }
        
        console.log('📱 Botones de WhatsApp activados después del escalamiento');
    }

    disableChatInput() {
        const input = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-message');
        
        if (input) {
            input.disabled = true;
            input.placeholder = 'Chat transferido a asesor humano...';
            input.style.backgroundColor = '#f5f5f5';
        }
        
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.style.backgroundColor = '#ccc';
        }
    }

    addMessage(sender, text, allowHTML = false, autoScroll = true) {
        const messages = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `${sender}-message`;
        messageDiv.style.cssText = `
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            ${sender === 'user' ? 
                'background: #667eea; color: white; margin-left: auto; text-align: right;' : 
                'background: white; border: 1px solid #eee;'
            }
        `;
        
        if (allowHTML) {
            messageDiv.innerHTML = text;
        } else {
            messageDiv.textContent = text;
        }
        
        messages.appendChild(messageDiv);
        
        if (autoScroll) {
            messages.scrollTop = messages.scrollHeight;
        }
    }
}

// Inicializar widget cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    const chatWidget = new MiztonChatWidget();
    
    // Verificar si debe activarse basado en el referido
    const referralCode = chatWidget.referralCode;
    chatWidget.checkIfShouldActivate(referralCode);
});
