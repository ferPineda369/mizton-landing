/**
 * Facebook Pixel Events para Mizton Landing
 * Eventos de conversión y seguimiento
 */

// Verificar que fbq esté disponible
function waitForFbq(callback) {
    if (typeof fbq !== 'undefined') {
        callback();
    } else {
        setTimeout(() => waitForFbq(callback), 100);
    }
}

// Inicializar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    waitForFbq(function() {
        console.log('🔥 Facebook Pixel Events iniciado');
        initializePixelEvents();
    });
});

function initializePixelEvents() {
    
    // EVENTO: Lead - Cuando alguien ingresa email en el chat
    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.includes('@')) {
                fbq('track', 'Lead', {
                    content_name: 'Email Capture Chat',
                    content_category: 'Lead Generation',
                    value: 1,
                    currency: 'USD'
                });
                console.log('📧 Pixel Event: Lead (Email Chat)');
            }
        });
    }
    
    // EVENTO: InitiateCheckout - Botones "Quiero Unirme"
    const joinButtons = document.querySelectorAll('a[href*="#unirse"], a[href*="#registro"]');
    joinButtons.forEach(button => {
        button.addEventListener('click', function() {
            fbq('track', 'InitiateCheckout', {
                content_name: 'Quiero Unirme Button',
                content_category: 'Registration Intent',
                value: 50,
                currency: 'USD'
            });
            console.log('🚀 Pixel Event: InitiateCheckout');
        });
    });
    
    // EVENTO: Contact - Botones de WhatsApp
    const whatsappButtons = document.querySelectorAll('a[href*="whatsapp"], a[href*="#whatsapp"]');
    whatsappButtons.forEach(button => {
        button.addEventListener('click', function() {
            fbq('track', 'Contact', {
                content_name: 'WhatsApp Contact',
                content_category: 'Support Contact',
                value: 1,
                currency: 'USD'
            });
            console.log('💬 Pixel Event: Contact (WhatsApp)');
        });
    });
    
    // EVENTO: ViewContent - Scroll profundo (75% de la página)
    let deepScrollTracked = false;
    window.addEventListener('scroll', function() {
        if (!deepScrollTracked) {
            const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent >= 75) {
                fbq('track', 'ViewContent', {
                    content_name: 'Deep Page Engagement',
                    content_category: 'Page Engagement',
                    value: 1,
                    currency: 'USD'
                });
                console.log('📖 Pixel Event: ViewContent (75% scroll)');
                deepScrollTracked = true;
            }
        }
    });
    
    // EVENTO: CompleteRegistration - Cuando se completa el flujo del chat
    // Este se puede activar desde el chat-widget cuando se complete el proceso
    window.trackPixelRegistration = function(email, referralCode) {
        fbq('track', 'CompleteRegistration', {
            content_name: 'Chat Registration Complete',
            content_category: 'Registration',
            value: 50,
            currency: 'USD',
            email: email,
            referral_code: referralCode
        });
        console.log('✅ Pixel Event: CompleteRegistration');
    };
    
    // EVENTO: Search - Cuando usan el chat para hacer preguntas
    window.trackPixelSearch = function(query) {
        fbq('track', 'Search', {
            search_string: query,
            content_category: 'Chat Interaction',
            value: 1,
            currency: 'USD'
        });
        console.log('🔍 Pixel Event: Search - ' + query);
    };
    
    // EVENTO: AddToWishlist - Interés en membresía específica
    const membershipButtons = document.querySelectorAll('.btn-primary');
    membershipButtons.forEach(button => {
        button.addEventListener('click', function() {
            const buttonText = this.textContent.trim();
            if (buttonText.includes('Unirme') || buttonText.includes('Comenzar')) {
                fbq('track', 'AddToWishlist', {
                    content_name: 'Membership Interest',
                    content_category: 'Membership',
                    value: 50,
                    currency: 'USD'
                });
                console.log('⭐ Pixel Event: AddToWishlist (Membership)');
            }
        });
    });
    
    // EVENTO: Purchase - Para cuando se complete una compra real (futuro)
    window.trackPixelPurchase = function(value, transactionId) {
        fbq('track', 'Purchase', {
            value: value,
            currency: 'USD',
            transaction_id: transactionId,
            content_name: 'Mizton Membership',
            content_category: 'Membership Purchase'
        });
        console.log('💰 Pixel Event: Purchase - $' + value);
    };
}

// Función para eventos personalizados
window.trackCustomPixelEvent = function(eventName, parameters = {}) {
    if (typeof fbq !== 'undefined') {
        fbq('trackCustom', eventName, parameters);
        console.log('🎯 Custom Pixel Event: ' + eventName, parameters);
    }
};
