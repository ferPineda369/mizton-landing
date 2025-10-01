/**
 * Facebook Pixel Proxy - Evita bloqueadores de anuncios
 * Carga el pixel de forma alternativa cuando es bloqueado
 */

(function() {
    'use strict';
    
    const PIXEL_ID = '684765634652448';
    const FB_EVENTS_URL = 'https://connect.facebook.net/en_US/fbevents.js';
    const FALLBACK_URL = 'https://www.facebook.com/tr';
    
    // Función para verificar si fbq está disponible
    function isFbqAvailable() {
        return typeof window.fbq !== 'undefined' && window.fbq.loaded;
    }
    
    // Función para cargar el pixel de Facebook
    function loadFacebookPixel() {
        return new Promise((resolve, reject) => {
            // Crear el script
            const script = document.createElement('script');
            script.async = true;
            script.src = FB_EVENTS_URL;
            
            script.onload = () => {
                console.log('✅ Facebook Pixel cargado exitosamente');
                initializeFbq();
                resolve(true);
            };
            
            script.onerror = () => {
                console.warn('⚠️ Facebook Pixel bloqueado, usando fallback');
                reject(false);
            };
            
            // Insertar el script
            const firstScript = document.getElementsByTagName('script')[0];
            firstScript.parentNode.insertBefore(script, firstScript);
            
            // Timeout de 5 segundos
            setTimeout(() => {
                if (!isFbqAvailable()) {
                    reject(false);
                }
            }, 5000);
        });
    }
    
    // Inicializar fbq
    function initializeFbq() {
        if (typeof window.fbq === 'undefined') {
            // Crear fbq si no existe
            window.fbq = function() {
                if (window.fbq.callMethod) {
                    window.fbq.callMethod.apply(window.fbq, arguments);
                } else {
                    window.fbq.queue.push(arguments);
                }
            };
            
            if (!window._fbq) window._fbq = window.fbq;
            window.fbq.push = window.fbq;
            window.fbq.loaded = true;
            window.fbq.version = '2.0';
            window.fbq.queue = [];
        }
        
        // Inicializar el pixel
        window.fbq('init', PIXEL_ID);
        window.fbq('track', 'PageView');
        
        console.log('🔥 Facebook Pixel inicializado - ID:', PIXEL_ID);
    }
    
    // Fallback usando imagen pixel
    function fallbackPixelTracking() {
        console.log('📡 Usando fallback pixel tracking');
        
        // Crear función fbq mock
        window.fbq = function(action, event, params = {}) {
            if (action === 'track' || action === 'trackCustom') {
                sendPixelEvent(event, params);
            } else if (action === 'init') {
                console.log('🔄 Pixel inicializado en modo fallback');
            }
        };
        
        // Disparar PageView inicial
        sendPixelEvent('PageView', {});
    }
    
    // Enviar evento usando imagen pixel
    function sendPixelEvent(eventName, params = {}) {
        try {
            const img = new Image();
            const baseUrl = FALLBACK_URL;
            const urlParams = new URLSearchParams({
                id: PIXEL_ID,
                ev: eventName,
                noscript: '1',
                ...params
            });
            
            img.src = `${baseUrl}?${urlParams.toString()}`;
            img.style.display = 'none';
            document.body.appendChild(img);
            
            console.log(`📊 Pixel Event (fallback): ${eventName}`, params);
            
            // Remover imagen después de 1 segundo
            setTimeout(() => {
                if (img.parentNode) {
                    img.parentNode.removeChild(img);
                }
            }, 1000);
            
        } catch (error) {
            console.error('❌ Error enviando pixel event:', error);
        }
    }
    
    // Función principal
    async function initializePixel() {
        try {
            // Intentar cargar el pixel normal
            await loadFacebookPixel();
            
            // Verificar que funciona
            setTimeout(() => {
                if (!isFbqAvailable()) {
                    console.warn('⚠️ Pixel no disponible, usando fallback');
                    fallbackPixelTracking();
                }
            }, 1000);
            
        } catch (error) {
            // Si falla, usar fallback
            fallbackPixelTracking();
        }
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePixel);
    } else {
        initializePixel();
    }
    
    // Exportar función para verificar estado
    window.checkPixelStatus = function() {
        const status = {
            fbqAvailable: isFbqAvailable(),
            pixelId: PIXEL_ID,
            mode: isFbqAvailable() ? 'normal' : 'fallback'
        };
        console.log('📊 Pixel Status:', status);
        return status;
    };
    
})();
