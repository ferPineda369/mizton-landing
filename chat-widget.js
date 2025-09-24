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
            
            if (data.success && data.data.use_chat) {
                // Activar chat automático
                this.referrerData = data.data;
                this.activate();
                console.log('🤖 Chat automatizado activado');
            } else {
                // Referidor tiene atención personal - NO activar chat
                console.log('🚫 Chat desactivado - Atención personal configurada');
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
                        Para brindarte mejor atención, ¿podrías compartir tu email?
                    </div>
                </div>
                
                <div id="chat-input-container" style="
                    padding: 15px;
                    border-top: 1px solid #eee;
                    display: flex;
                    gap: 10px;
                ">
                    <input 
                        id="chat-input" 
                        type="text" 
                        placeholder="Escribe tu mensaje..."
                        style="
                            flex: 1;
                            padding: 10px;
                            border: 1px solid #ddd;
                            border-radius: 20px;
                            outline: none;
                        "
                    />
                    <button id="send-message" style="
                        background: #667eea;
                        color: white;
                        border: none;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        cursor: pointer;
                    ">➤</button>
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
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;

        this.addMessage('user', message);
        input.value = '';

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
                    this.currentStep = 'chatting';
                    this.addMessage('bot', `¡Perfecto, ${message}! 🎉 Ya estás registrado. Ahora puedo ayudarte con cualquier pregunta sobre Mizton. ¿Qué te gustaría saber?`);
                } else {
                    this.addMessage('bot', 'Hubo un problema al registrar tu email. ¿Podrías intentar nuevamente?');
                }
            } catch (error) {
                console.error('Error guardando lead:', error);
                this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar más tarde?');
            }
        } else {
            this.addMessage('bot', 'Por favor ingresa un email válido (ejemplo: tu@email.com) para continuar.');
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
                this.addMessage('bot', data.data.response);
                
                // Verificar si requiere escalamiento a humano
                if (data.data.requires_human) {
                    setTimeout(() => {
                        this.escalateToHuman();
                    }, 2000); // Esperar 2 segundos antes de escalar
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
                this.addMessage('bot', 'Lo siento, no pude procesar tu mensaje. ¿Podrías reformularlo?');
            }
            
        } catch (error) {
            console.error('Error procesando mensaje:', error);
            this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar más tarde?');
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
            
            if (data.success && data.data.escalated) {
                const contactInfo = data.data;
                let escalationMessage = '';
                
                if (contactInfo.contact_method === 'whatsapp_personal') {
                    escalationMessage = `${contactInfo.message} 
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent('Hola! Vengo del chat de la landing page y me gustaría hablar con un asesor.')}" target="_blank" style="color: #667eea; text-decoration: underline;">
                    👤 Contactar a ${contactInfo.referrer_name || 'tu asesor'} por WhatsApp
                    </a>`;
                } else {
                    escalationMessage = `${contactInfo.message}
                    
                    🔗 <a href="https://wa.me/${contactInfo.contact_info}?text=${encodeURIComponent('Hola! Vengo del chat de la landing page y me gustaría hablar con un asesor.')}" target="_blank" style="color: #667eea; text-decoration: underline;">
                    📱 Contactar equipo de asesores por WhatsApp
                    </a>`;
                }
                
                this.addMessage('bot', escalationMessage, true); // true para permitir HTML
                
                // Deshabilitar input después del escalamiento
                this.disableChatInput();
                
            } else {
                this.addMessage('bot', 'Disculpa, hubo un problema al conectarte con un asesor. ¿Podrías intentar más tarde?');
            }
            
        } catch (error) {
            console.error('Error escalando a humano:', error);
            this.addMessage('bot', 'Disculpa, hay un problema técnico. ¿Podrías intentar contactarnos directamente por WhatsApp?');
        }
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

    addMessage(sender, text, allowHTML = false) {
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
        messages.scrollTop = messages.scrollHeight;
    }
}

// Inicializar widget cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    const chatWidget = new MiztonChatWidget();
    
    // Verificar si debe activarse basado en el referido
    const referralCode = chatWidget.referralCode;
    chatWidget.checkIfShouldActivate(referralCode);
});
