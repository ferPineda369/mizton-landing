/**
 * Mizton Presentation - Interactive Scripts
 * Funcionalidades específicas para la presentación de Mizton
 */

// Variables globales para navegación
let currentSlide = 0;
const totalSlides = 24;

// Timer management para secuencias de revelado
let activeRevealTimers = [];
let progressiveScrollAnimation = null;

// Estado global de controles de la presentación
const presentationState = {
    autoPlayMedia: true,   // toggle izquierda: auto-reproducir audio/video
    autoRun: false,        // toggle derecha: avanzar solo al terminar cada slide
    maxReachedSlide: 1,    // slide más lejano desbloqueado (0-1 libres: portada/intro; bloqueo desde 2)
    slideCompleted: false, // ¿terminó la reproducción del slide actual?
    completionEl: null,    // elemento <audio>/<video> observado
    completionHandler: null,
    completionTimer: null,
    autoRunTimer: null
};

function clearRevealTimers() {
    activeRevealTimers.forEach(timer => clearTimeout(timer));
    activeRevealTimers = [];
    if (progressiveScrollAnimation) {
        cancelAnimationFrame(progressiveScrollAnimation);
        progressiveScrollAnimation = null;
    }
}

// Configuración de slides con video (debe estar antes de goToSlide)
const VIDEO_SLIDES = {
    6: { videoId: 'video-slide-6', audioId: 'audio-slide-6' },
    7: { 
        videoId: 'video-slide-7', 
        audioIntroId: 'audio-slide-7-1',
        audioOutroId: 'audio-slide-7-2',
        newsVideosPath: 'videos/news/',
        newsVideosCount: 9,
        hasSequence: true
    }
};

function goToSlide(slideNumber) {
    if (slideNumber < 0 || slideNumber > totalSlides - 1) return;
    
    // Pausar todos los audios
    const allAudios = document.querySelectorAll('audio');
    allAudios.forEach(audio => {
        audio.pause();
        audio.currentTime = 0;
    });
    
    // Pausar todos los videos
    const allVideos = document.querySelectorAll('video');
    allVideos.forEach(video => {
        video.pause();
        video.currentTime = 0;
    });
    
    // Limpiar timers de secuencias de revelado anteriores
    clearRevealTimers();
    clearSlideCompletion();
    
    // Limpiar hints de navegación del slide anterior
    const nextBtn = document.getElementById('next-btn');
    const restartBtn = document.getElementById('restart-btn');
    if (nextBtn) nextBtn.classList.remove('hint-pulse');
    if (restartBtn) restartBtn.classList.remove('hint-pulse');
    
    const currentSlideEl = document.querySelector(`.slide[data-slide="${currentSlide}"]`);
    if (currentSlideEl) {
        currentSlideEl.classList.remove('active');
    }
    
    currentSlide = slideNumber;
    const newSlideEl = document.querySelector(`.slide[data-slide="${currentSlide}"]`);
    if (newSlideEl) {
        newSlideEl.classList.add('active');
    }
    
    // El slide actual (y los anteriores) quedan desbloqueados
    presentationState.maxReachedSlide = Math.max(presentationState.maxReachedSlide, currentSlide);
    
    // Reproducir audio si existe (excepto en slides con video que manejan audio diferido)
    const audioToPlay = document.getElementById(`audio-slide-${currentSlide}`);
    if (presentationState.autoPlayMedia && audioToPlay && !VIDEO_SLIDES[currentSlide]) {
        // Verificar si tiene delay configurado (slides 4 y 5)
        const delay = audioToPlay.getAttribute('data-delay');
        if (delay) {
            setTimeout(() => {
                audioToPlay.play().catch(e => console.log('Error reproduciendo audio:', e));
            }, parseInt(delay));
        } else {
            audioToPlay.play().catch(e => console.log('Error reproduciendo audio:', e));
        }
    }
    
    // Iniciar video si el slide tiene video configurado (con delay para permitir activar sonido)
    if (presentationState.autoPlayMedia && VIDEO_SLIDES[currentSlide]) {
        const videoConfig = VIDEO_SLIDES[currentSlide];
        
        // Slide 7: Secuencia especial audio1 -> video aleatorio -> audio2
        if (videoConfig.hasSequence && currentSlide === 7) {
            initSlide7Sequence(videoConfig);
        } else if (currentSlide === 6) {
            // Slide 6: Intentar reproducir con SONIDO primero
            const video = document.getElementById(videoConfig.videoId);
            const muteOverlay = document.getElementById('video-mute-overlay');
            if (video) {
                video.currentTime = 0;
                video.muted = false; // Intentar con sonido
                
                // Intentar reproducir inmediatamente con sonido
                video.play().then(() => {
                    // Éxito: reproduciendo con sonido
                    if (muteOverlay) muteOverlay.style.display = 'none';
                }).catch(e => {
                    // Falló: navegador bloqueó autoplay con sonido
                    console.log('Video autoplay with sound blocked:', e);
                    video.muted = true;
                    // Mostrar overlay para que el usuario active el sonido
                    if (muteOverlay) {
                        muteOverlay.style.display = 'flex';
                        muteOverlay.querySelector('.mute-text').textContent = '▶ Click para reproducir con sonido';
                    }
                    // No reproducir aún - esperar al clic del usuario
                });
            }
        } else {
            // Otros slides con video: comportamiento original (muteado)
            const video = document.getElementById(videoConfig.videoId);
            const muteOverlay = document.getElementById('video-mute-overlay');
            if (video) {
                video.currentTime = 0;
                video.muted = true;
                
                if (muteOverlay) {
                    muteOverlay.style.display = 'flex';
                }
                
                setTimeout(() => {
                    video.play().catch(e => console.log('Video autoplay blocked:', e));
                }, 2500);
            }
        }
    }
    
    const activeSlideEl = document.querySelector(`.slide[data-slide="${currentSlide}"]`);
    
    if (presentationState.autoPlayMedia) {
        // AUTO-PLAY ON: secuencias temporizadas + scroll progresivo
        if (activeSlideEl && activeSlideEl.querySelectorAll('[data-reveal-time]').length > 0) {
            initSlideRevealSequence(currentSlide);
        }
        if (activeSlideEl && activeSlideEl.hasAttribute('data-auto-scroll-duration')) {
            const duration = parseInt(activeSlideEl.getAttribute('data-auto-scroll-duration'));
            initProgressiveScroll(activeSlideEl, duration);
        }
        if (currentSlide === 22) initSlide22Scroll();
        if (currentSlide === 23) initSlide23DualAudio();
        hideManualAudioButtons();
    } else {
        // AUTO-PLAY OFF: sin reveals automáticos, mostrar botones de audio manuales
        resetRevealStyles(currentSlide);
        showManualAudioButtons(currentSlide);
        
        // Slide 6 en modo manual: mostrar video con controles, SIN mute
        if (currentSlide === 6) {
            const video = document.getElementById('video-slide-6');
            const muteOverlay = document.getElementById('video-mute-overlay');
            if (video) {
                video.style.display = 'block';
                video.setAttribute('controls', 'true');
                video.muted = false;
                video.currentTime = 0;
                video.play().catch(e => console.log('Video manual play:', e));
            }
            if (muteOverlay) muteOverlay.style.display = 'none';
        }
        
        // Slide 7 en modo manual: manejado por initSlide7Sequence
        if (currentSlide === 7) {
            const config = VIDEO_SLIDES[7];
            if (config) initSlide7Sequence(config);
        }
    }
    
    // Configurar detección de fin de reproducción (desbloqueo/auto-run)
    setupSlideCompletion(currentSlide);
    
    updateSlideDots();
    updateProgressBar();
    updateNavLockState();
}

function nextSlide() {
    const lastSlide = totalSlides - 1;
    if (currentSlide >= lastSlide) return;
    // Bloqueado: no se puede avanzar a un slide aún no liberado
    if ((currentSlide + 1) > presentationState.maxReachedSlide) return;
    // Limpiar hint del botón next al usarlo
    const nextBtn = document.getElementById('next-btn');
    if (nextBtn) nextBtn.classList.remove('hint-pulse');
    goToSlide(currentSlide + 1);
}

function prevSlide() {
    if (currentSlide > 1) {
        goToSlide(currentSlide - 1);
    }
}

function createSlideDots() {
    const dotsContainer = document.getElementById('slide-dots');
    if (!dotsContainer) return;
    
    dotsContainer.innerHTML = '';
    
    for (let i = 0; i < totalSlides; i++) {
        const numBtn = document.createElement('button');
        numBtn.className = 'slide-number-btn';
        numBtn.textContent = i;
        numBtn.setAttribute('data-slide-number', i);
        if (i === currentSlide) {
            numBtn.classList.add('active');
        }
        if (i > presentationState.maxReachedSlide) {
            numBtn.classList.add('locked');
            numBtn.disabled = true;
        }
        numBtn.addEventListener('click', () => {
            if (i > presentationState.maxReachedSlide) return;
            goToSlide(i);
        });
        dotsContainer.appendChild(numBtn);
    }
    
    updateSlideDots();
}

function updateSlideDots() {
    const numBtns = document.querySelectorAll('.slide-number-btn');
    const visibleCount = 5; // Mostrar 5 números a la vez
    const halfVisible = Math.floor(visibleCount / 2); // 2
    
    numBtns.forEach((btn, index) => {
        // Actualizar estado activo
        if (index === currentSlide) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
        
        // Calcular rango visible
        let startVisible = currentSlide - halfVisible;
        let endVisible = currentSlide + halfVisible;
        
        // Ajustar para los bordes
        if (startVisible < 0) {
            endVisible += Math.abs(startVisible);
            startVisible = 0;
        }
        if (endVisible >= totalSlides) {
            startVisible -= (endVisible - totalSlides + 1);
            endVisible = totalSlides - 1;
            if (startVisible < 0) startVisible = 0;
        }
        
        // Mostrar/ocultar según esté en el rango visible
        if (index >= startVisible && index <= endVisible) {
            btn.style.display = 'flex';
        } else {
            btn.style.display = 'none';
        }
    });
    
    // Scroll automático para mantener el número activo centrado
    const dotsContainer = document.getElementById('slide-dots');
    const activeBtn = dotsContainer?.querySelector('.slide-number-btn.active');
    if (dotsContainer && activeBtn) {
        const containerRect = dotsContainer.getBoundingClientRect();
        const btnRect = activeBtn.getBoundingClientRect();
        const scrollLeft = btnRect.left - containerRect.left + dotsContainer.scrollLeft - (containerRect.width / 2) + (btnRect.width / 2);
        dotsContainer.scrollTo({ left: scrollLeft, behavior: 'smooth' });
    }
    
    // Actualizar indicador de número en mobile
    const indicator = document.getElementById('slide-number-indicator');
    if (indicator) {
        indicator.textContent = currentSlide;
    }
}

function updateProgressBar() {
    const progress = (currentSlide / (totalSlides - 1)) * 100;
    const progressFill = document.getElementById('progress-fill');
    if (progressFill) progressFill.style.width = progress + '%';
}

function updateNavLockState() {
    const lastSlide = totalSlides - 1;
    const maxReached = presentationState.maxReachedSlide;
    
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (prevBtn) prevBtn.disabled = currentSlide <= 1;
    if (nextBtn) {
        nextBtn.disabled = currentSlide >= lastSlide || (currentSlide + 1) > maxReached;
    }
    
    // Bloquear puntos de slides aún no liberados
    document.querySelectorAll('.dot').forEach((dot, index) => {
        if (index > maxReached) {
            dot.classList.add('locked');
        } else {
            dot.classList.remove('locked');
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// DETECCIÓN DE FIN DE REPRODUCCIÓN + AUTO-RUN + BLOQUEO PROGRESIVO
// ═══════════════════════════════════════════════════════════════════

// Devuelve el elemento de audio/video cuyo final marca el fin del slide
function getCompletionMedia(slideNum) {
    if (slideNum === 23) return document.getElementById('audio-slide-23-2');
    if (slideNum === 7) return document.getElementById('audio-slide-7-2');
    if (slideNum === 6) {
        return document.getElementById('audio-slide-6') || document.getElementById('video-slide-6');
    }
    return document.getElementById(`audio-slide-${slideNum}`) || null;
}

function clearSlideCompletion() {
    if (presentationState.completionEl && presentationState.completionHandler) {
        presentationState.completionEl.removeEventListener('ended', presentationState.completionHandler);
    }
    if (presentationState.completionTimer) {
        clearTimeout(presentationState.completionTimer);
    }
    if (presentationState.autoRunTimer) {
        clearTimeout(presentationState.autoRunTimer);
    }
    presentationState.completionEl = null;
    presentationState.completionHandler = null;
    presentationState.completionTimer = null;
    presentationState.autoRunTimer = null;
    presentationState.slideCompleted = false;
}

function setupSlideCompletion(slideNum) {
    // Si el auto-play de medios está desactivado, liberar navegación de inmediato
    if (!presentationState.autoPlayMedia) {
        onSlideComplete(slideNum);
        return;
    }
    
    const media = getCompletionMedia(slideNum);
    if (media) {
        const handler = function() {
            media.removeEventListener('ended', handler);
            onSlideComplete(slideNum);
        };
        presentationState.completionEl = media;
        presentationState.completionHandler = handler;
        media.addEventListener('ended', handler);
    } else {
        // Slides sin medio (ej. portada): completar tras breve tiempo
        presentationState.completionTimer = setTimeout(() => onSlideComplete(slideNum), 4000);
    }
}

function onSlideComplete(slideNum) {
    // Ignorar si ya cambiamos de slide o ya se marcó completo
    if (slideNum !== currentSlide || presentationState.slideCompleted) return;
    presentationState.slideCompleted = true;
    
    const lastSlide = totalSlides - 1;
    
    // Desbloquear el siguiente slide
    if (currentSlide + 1 <= lastSlide) {
        presentationState.maxReachedSlide = Math.max(presentationState.maxReachedSlide, currentSlide + 1);
    }
    updateNavLockState();
    
    // Auto-run: avanzar automáticamente al siguiente slide
    if (presentationState.autoRun && currentSlide < lastSlide) {
        presentationState.autoRunTimer = setTimeout(() => {
            if (presentationState.autoRun && currentSlide < lastSlide) {
                goToSlide(currentSlide + 1);
            }
        }, 1200);
    }
}

// ═══════════════════════════════════════════════════════════════════
// BOTONES MANUALES DE AUDIO (modo manual / autoPlayMedia OFF)
// ═══════════════════════════════════════════════════════════════════

function showManualAudioButtons(slideNum) {
    const container = document.getElementById('manual-audio-btns');
    if (!container) return;
    container.innerHTML = '';
    
    const slide = document.querySelector(`.slide[data-slide="${slideNum}"]`);
    if (!slide) return;
    
    const audios = slide.querySelectorAll('audio');
    if (audios.length === 0) return;
    
    const buttons = [];
    const nextNavBtn = document.getElementById('next-btn');
    const restartBtn = document.getElementById('restart-btn');
    
    // Función para agregar/quitar efecto pulse
    const setHintPulse = (element, active) => {
        if (element) {
            if (active) element.classList.add('hint-pulse');
            else element.classList.remove('hint-pulse');
        }
    };
    
    // Función para marcar slide como completado (hint en > o ⟲)
    const markSlideComplete = () => {
        // Quitar hints de todos los botones de audio
        buttons.forEach(b => setHintPulse(b, false));
        
        // Slide 23: hint en botón reiniciar
        if (slideNum === 23 && restartBtn) {
            setHintPulse(restartBtn, true);
        } else if (nextNavBtn) {
            // Otros slides: hint en botón siguiente
            setHintPulse(nextNavBtn, true);
        }
    };
    
    // Slide 6: bloquear todos los botones hasta que termine el video
    const isSlide6 = slideNum === 6;
    const isSlide7 = slideNum === 7;
    const isSlide23 = slideNum === 23;
    const video6 = isSlide6 ? document.getElementById('video-slide-6') : null;
    const video7 = isSlide7 ? document.getElementById('video-slide-7') : null;
    
    // Quitar hint de navegación al iniciar
    setHintPulse(nextNavBtn, false);
    setHintPulse(restartBtn, false);
    
    audios.forEach((audio, index) => {
        const btn = document.createElement('button');
        btn.className = 'manual-audio-btn';
        btn.textContent = `▶ ${index + 1}`;
        btn.title = `Reproducir audio ${index + 1}`;
        
        // Configuración inicial de bloqueo
        const shouldBlockInitially = isSlide6 ? true : (index > 0);
        if (shouldBlockInitially) {
            btn.disabled = true;
            btn.classList.add('locked');
        }
        
        // Slide 6: habilitar ▶1 cuando termine el video + hint
        if (isSlide6 && video6 && index === 0) {
            const onVideo6Ended = () => {
                btn.disabled = false;
                btn.classList.remove('locked');
                setHintPulse(btn, true); // Hint en ▶1
                video6.removeEventListener('ended', onVideo6Ended);
            };
            video6.addEventListener('ended', onVideo6Ended);
            if (video6.ended || video6.currentTime >= video6.duration - 0.1) {
                onVideo6Ended();
            }
        }
        
        // Slide 7: lógica especial (▶1 → video → ▶2 → >)
        if (isSlide7 && video7) {
            if (index === 0) {
                // ▶1: hint inmediato
                setHintPulse(btn, true);
            }
            if (index === 1) {
                // ▶2: bloqueado hasta que termine el video
                btn.disabled = true;
                btn.classList.add('locked');
            }
        }
        
        // Slides S01-S05, S08-S22: hint en ▶1 inmediatamente
        if (!isSlide6 && !isSlide7 && index === 0) {
            setHintPulse(btn, true);
        }
        
        // Slide 23: hint en ▶1 inmediatamente
        if (slideNum === 23 && index === 0) {
            setHintPulse(btn, true);
        }
        
        btn.addEventListener('click', function() {
            // Quitar hint del botón actual al hacer clic
            setHintPulse(btn, false);
            // Pausar otros audios del slide
            audios.forEach(a => { a.pause(); a.currentTime = 0; });
            audio.play().catch(e => console.log('Manual audio error:', e));
            // Visual feedback
            container.querySelectorAll('.manual-audio-btn').forEach(b => b.classList.remove('playing'));
            btn.classList.add('playing');
        });
        
        // Evento play: ejecutar animaciones slide 22
        audio.addEventListener('play', function onPlay() {
            if (slideNum === 22 && window.miztonPresentation) {
                window.miztonPresentation.animateGlobalNet();
                window.miztonPresentation.animateStats();
            }
        });
        
        // Evento ended: manejar flujo de hints
        audio.addEventListener('ended', function onManualEnd() {
            audio.removeEventListener('ended', onManualEnd);
            btn.classList.remove('playing');
            btn.classList.add('done');
            
            // Slide 6: ▶1 termina → hint en >
            if (isSlide6 && index === 0) {
                markSlideComplete();
            }
            // Slides S01-S05, S08-S22: ▶1 termina → hint en >
            else if (!isSlide6 && !isSlide7 && !isSlide23 && index === 0) {
                markSlideComplete();
            }
            // Slide 7: ▶1 termina → reproducir video
            else if (isSlide7 && index === 0 && video7) {
                // Hint se transfiere automáticamente al video (visual)
                video7.play().catch(e => console.log('Video play error:', e));
            }
            // Slide 7: ▶2 termina → hint en >
            else if (isSlide7 && index === 1) {
                markSlideComplete();
            }
            // Slide 23: ▶1 termina → hint en ▶2
            else if (slideNum === 23 && index === 0 && buttons[1]) {
                buttons[1].disabled = false;
                buttons[1].classList.remove('locked');
                setHintPulse(buttons[1], true);
            }
            // Slide 23: ▶2 termina → hint en ⟲
            else if (slideNum === 23 && index === 1) {
                markSlideComplete();
            }
            // Habilitar siguiente botón de audio si existe
            else {
                const nextBtn = buttons[index + 1];
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.classList.remove('locked');
                }
            }
        });
        
        buttons.push(btn);
        container.appendChild(btn);
    });
    
    // Slide 7: manejar fin del video (habilitar ▶2 con hint)
    if (isSlide7 && video7 && buttons[1]) {
        video7.addEventListener('ended', () => {
            buttons[1].disabled = false;
            buttons[1].classList.remove('locked');
            setHintPulse(buttons[1], true); // Hint en ▶2
        });
    }
}

function hideManualAudioButtons() {
    const container = document.getElementById('manual-audio-btns');
    if (container) container.innerHTML = '';
}

// Restaurar visibilidad de elementos ocultos por secuencias de revelado
function resetRevealStyles(slideNum) {
    const slide = document.querySelector(`.slide[data-slide="${slideNum}"]`);
    if (!slide) return;
    
    slide.querySelectorAll('[data-reveal-time]').forEach(el => {
        el.style.opacity = '';
        el.style.filter = '';
        el.style.transform = '';
        el.classList.add('revealed');
        const splash = el.querySelector('.vision-splash');
        if (splash) splash.style.opacity = '0';
    });
}

// ═══════════════════════════════════════════════════════════════════
// CONTROLES GENERALES (toggles, reiniciar, pantalla completa)
// ═══════════════════════════════════════════════════════════════════

function initPresentationControls() {
    const autoPlayToggle = document.getElementById('toggle-autoplay-media');
    const autoRunBtn = document.getElementById('autorun-btn');
    const restartBtn = document.getElementById('restart-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    
    // Toggle: auto-reproducir audio/video
    if (autoPlayToggle) {
        autoPlayToggle.addEventListener('change', function() {
            presentationState.autoPlayMedia = this.checked;
            
            // Actualizar label del footer
            const footerLabel = document.querySelector('.footer-label');
            if (footerLabel) footerLabel.textContent = this.checked ? 'AUTO' : 'MANUAL';
            
            if (!this.checked) {
                // OFF: pausar toda la multimedia, limpiar reveals, mostrar botones manuales
                document.querySelectorAll('audio').forEach(a => { a.pause(); a.currentTime = 0; });
                document.querySelectorAll('video').forEach(v => { v.pause(); v.currentTime = 0; });
                clearRevealTimers();
                resetRevealStyles(currentSlide);
                showManualAudioButtons(currentSlide);
                onSlideComplete(currentSlide); // liberar navegación
                
                // Desactivar y BLOQUEAR Auto-Play en modo MANUAL
                if (presentationState.autoRun) {
                    presentationState.autoRun = false;
                }
                if (autoRunBtn) {
                    autoRunBtn.classList.remove('active');
                    autoRunBtn.textContent = '▶ Auto-Play';
                    autoRunBtn.disabled = true; // Bloquear botón
                    autoRunBtn.classList.add('disabled');
                }
                if (presentationState.autoRunTimer) {
                    clearTimeout(presentationState.autoRunTimer);
                    presentationState.autoRunTimer = null;
                }
                
                // Si estamos en slide 6, mostrar video con controles nativos, SIN mute
                if (currentSlide === 6) {
                    const video = document.getElementById('video-slide-6');
                    const muteOverlay = document.getElementById('video-mute-overlay');
                    if (video) {
                        video.style.display = 'block';
                        video.setAttribute('controls', 'true');
                        video.muted = false;
                        video.play().catch(e => console.log('Video toggle OFF play:', e));
                    }
                    if (muteOverlay) muteOverlay.style.display = 'none';
                }
                
                // Si estamos en slide 7, mostrar video con controles y botón de cargar video
                if (currentSlide === 7) {
                    const video = document.getElementById('video-slide-7');
                    const loadingOverlay = document.getElementById('video-7-loading');
                    const loadRandomBtn = document.getElementById('load-random-video-btn');
                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                    if (video) {
                        video.style.display = 'block';
                        video.setAttribute('controls', 'true');
                    }
                    if (loadRandomBtn) loadRandomBtn.style.display = 'inline-block';
                }
            } else {
                // ON: ocultar botones manuales, reiniciar slide con auto-play, DESBLOQUEAR botón Auto-Play
                hideManualAudioButtons();
                if (autoRunBtn) {
                    autoRunBtn.disabled = false;
                    autoRunBtn.classList.remove('disabled');
                }
                goToSlide(currentSlide);
            }
        });
    }
    
    // Botón: Auto-Play / Pause (reproducción automática de principio a fin)
    if (autoRunBtn) {
        autoRunBtn.addEventListener('click', function() {
            presentationState.autoRun = !presentationState.autoRun;
            this.classList.toggle('active', presentationState.autoRun);
            this.textContent = presentationState.autoRun ? '⏸ Pause' : '▶ Auto-Play';
            
            if (presentationState.autoRun) {
                const lastSlide = totalSlides - 1;
                if (currentSlide === 0) {
                    goToSlide(1);
                } else if (presentationState.slideCompleted && currentSlide < lastSlide) {
                    goToSlide(currentSlide + 1);
                }
            } else if (presentationState.autoRunTimer) {
                clearTimeout(presentationState.autoRunTimer);
                presentationState.autoRunTimer = null;
            }
        });
    }
    
    // Botón: reiniciar presentación (volver al slide 0 en modo AUTO)
    if (restartBtn) {
        restartBtn.addEventListener('click', function() {
            restartBtn.classList.remove('hint-pulse');
            
            // Configurar modo AUTO
            presentationState.autoPlayMedia = true;
            const autoPlayToggle = document.getElementById('toggle-autoplay-media');
            const autoRunBtn = document.getElementById('autorun-btn');
            const footerLabel = document.querySelector('.footer-label');
            
            if (autoPlayToggle) autoPlayToggle.checked = true;
            if (footerLabel) footerLabel.textContent = 'AUTO';
            if (autoRunBtn) {
                autoRunBtn.disabled = false;
                autoRunBtn.classList.remove('disabled');
            }
            
            // Ocultar botones manuales y reiniciar al slide 0
            hideManualAudioButtons();
            goToSlide(0);
        });
    }
    
    // Botón: pantalla completa
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        document.addEventListener('fullscreenchange', function() {
            fullscreenBtn.classList.toggle('active', !!document.fullscreenElement);
        });
    }
    
    // Verificar estado inicial del toggle: si está en MANUAL, bloquear Auto-Play
    if (autoPlayToggle && !autoPlayToggle.checked && autoRunBtn) {
        autoRunBtn.disabled = true;
        autoRunBtn.classList.add('disabled');
        presentationState.autoPlayMedia = false;
        const footerLabel = document.querySelector('.footer-label');
        if (footerLabel) footerLabel.textContent = 'MANUAL';
    }
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(e => console.log('Fullscreen error:', e));
    } else {
        document.exitFullscreen();
    }
}

function handleKeyboard(e) {
    switch(e.key) {
        case 'ArrowRight':
        case 'ArrowDown':
        case ' ':
            e.preventDefault();
            nextSlide();
            break;
        case 'ArrowLeft':
        case 'ArrowUp':
            e.preventDefault();
            prevSlide();
            break;
        case 'Home':
            e.preventDefault();
            goToSlide(1);
            break;
        case 'End':
            e.preventDefault();
            goToSlide(presentationState.maxReachedSlide);
            break;
    }
}

(function() {
    'use strict';

    // =========================================================================
    // ANIMACIONES AL CARGAR SLIDES
    // =========================================================================
    
    // Animación para el diagrama del modelo
    function animateModelDiagram() {
        const points = document.querySelectorAll('.model-point');
        points.forEach((point, index) => {
            point.style.opacity = '0';
            point.style.transform = point.style.transform + ' scale(0)';
            
            setTimeout(() => {
                point.style.transition = 'all 0.5s ease';
                point.style.opacity = '1';
                point.style.transform = point.style.transform.replace('scale(0)', 'scale(1)');
            }, index * 200);
        });
    }

    // Animación para el pool líquido
    function animatePoolLiquid() {
        const liquid = document.querySelector('.pool-liquid');
        if (liquid) {
            liquid.style.height = '0%';
            setTimeout(() => {
                liquid.style.transition = 'height 2s ease-out';
                liquid.style.height = '70%';
            }, 300);
        }
    }

    // Animación para timeline items
    function animateTimeline() {
        const items = document.querySelectorAll('.timeline-item, .process-phase, .formation-step');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-30px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.5s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 150);
        });
    }

    // Animación para tarjetas de proyectos
    function animateProjectCards() {
        const cards = document.querySelectorAll('.project-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 200);
        });
        
        // Inicializar flechas después de la animación
        setTimeout(() => {
            initProjectArrows();
        }, cards.length * 200 + 100);
    }

    // Animación para beneficios
    function animateBenefits() {
        const items = document.querySelectorAll('.benefit-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'scale(1)';
            }, index * 100);
        });
    }

    // Animación para visión grid
    function animateVisionGrid() {
        const cards = document.querySelectorAll('.vision-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'rotateY(90deg)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'rotateY(0)';
            }, index * 150);
        });
    }

    // Animación para niveles de vesting
    function animateVestingLevels() {
        const levels = document.querySelectorAll('.vesting-level');
        levels.forEach((level, index) => {
            level.style.opacity = '0';
            level.style.transform = 'translateY(50px)';
            
            setTimeout(() => {
                level.style.transition = 'all 0.5s ease';
                level.style.opacity = '1';
                level.style.transform = 'translateY(0)';
            }, index * 200);
        });
    }

    // Animación para estadísticas
    function animateStats() {
        const stats = document.querySelectorAll('.stat-number, .gstat-value');
        stats.forEach(stat => {
            const finalValue = stat.textContent.trim();
            
            // Si contiene 'M' (millones), no animar numéricamente - mostrar directamente con efecto fade
            if (finalValue.includes('M')) {
                stat.style.opacity = '0';
                setTimeout(() => {
                    stat.style.transition = 'opacity 0.5s ease';
                    stat.style.opacity = '1';
                }, 100);
                return;
            }
            
            stat.textContent = '0';
            
            let current = 0;
            const increment = finalValue.includes(',') ? 205 : 
                             finalValue.includes('+') ? 100000 : 50;
            const target = parseInt(finalValue.replace(/[^0-9]/g, '')) || 0;
            
            if (target === 0) {
                stat.textContent = finalValue;
                return;
            }
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = finalValue;
                    clearInterval(timer);
                } else {
                    stat.textContent = current.toLocaleString() + (finalValue.includes('+') ? '+' : '');
                }
            }, 30);
        });
    }

    // Animación para la red global con paquetes animados
    function animateGlobalNet() {
        const levels = document.querySelectorAll('.net-branch');
        levels.forEach((level, index) => {
            level.style.opacity = '0';
            level.style.transform = 'translateX(-50px)';
            
            setTimeout(() => {
                level.style.transition = 'all 0.4s ease';
                level.style.opacity = '1';
                level.style.transform = 'translateX(0)';
            }, index * 200);
        });

        // Animar paquetes individualmente con data-delay
        const packs = document.querySelectorAll('.pack-item');
        packs.forEach(pack => {
            const delay = parseInt(pack.dataset.delay) || 0;
            
            setTimeout(() => {
                pack.classList.add('visible');
                
                // Efecto especial para paquetes del usuario
                if (pack.classList.contains('your-pack')) {
                    pack.style.animation = 'pulse 1s ease-in-out';
                }
            }, delay);
        });
    }

    // Animación para la red personal
    function animatePersonalNet() {
        const nodes = document.querySelectorAll('.net-node:not(.you)');
        nodes.forEach((node, index) => {
            node.style.opacity = '0';
            node.style.transform = 'scale(0)';
            
            setTimeout(() => {
                node.style.transition = 'all 0.3s ease';
                node.style.opacity = '1';
                node.style.transform = 'scale(1)';
            }, index * 100);
        });

        // Nodo del usuario con glow
        const youNode = document.querySelector('.net-node.you');
        if (youNode) {
            youNode.style.boxShadow = '0 0 0 rgba(0, 229, 255, 0)';
            setTimeout(() => {
                youNode.style.transition = 'box-shadow 1s ease';
                youNode.style.boxShadow = '0 0 30px rgba(0, 229, 255, 0.5)';
            }, nodes.length * 100);
        }
    }

    // =========================================================================
    // DETECCIÓN DE SLIDES ACTIVAS
    // =========================================================================
    
    // Observador para detectar cuando un slide está activo
    const slideObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const slide = mutation.target;
                if (slide.classList.contains('active')) {
                    const slideNum = parseInt(slide.dataset.slide);
                    
                    // Ejecutar animaciones según el slide
                    // Solo para slides que NO usan initSlideRevealSequence
                    setTimeout(() => {
                        switch(slideNum) {
                            case 11: // Pool Global
                                if (presentationState.autoPlayMedia) animatePoolLiquid();
                                break;
                            case 21: // Red Personal (solo net nodes, stat-boxes via reveal)
                                if (presentationState.autoPlayMedia) animatePersonalNet();
                                break;
                            case 22: // Red Global - animación se ejecuta al reproducir audio (ver abajo)
                                break;
                        }
                    }, 300);
                }
            }
        });
    });

    // Observar todos los slides
    function initSlideObserver() {
        const slides = document.querySelectorAll('.slide');
        slides.forEach(slide => {
            slideObserver.observe(slide, { attributes: true });
        });
    }

    // =========================================================================
    // EFECTOS DE HOVER Y TRANSICIONES
    // =========================================================================
    
    function initHoverEffects() {
        // Efecto ripple en botones
        document.querySelectorAll('.cta-button, .intro-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = e.clientX - rect.left - size/2 + 'px';
                ripple.style.top = e.clientY - rect.top - size/2 + 'px';
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Parallax suave en hover para tarjetas
        document.querySelectorAll('.vision-card, .project-card, .participation-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
            });
        });
    }

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================
    
    function init() {
        // Inicializar navegación
        createSlideDots();
        updateProgressBar();
        updateNavLockState();
        
        // Inicializar listener para animación de slide 22 (ejecutar al reproducir audio)
        initSlide22AudioAnimation();
        
        // Event listeners para botones
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Inicializar controles generales (toggles, reiniciar, pantalla completa)
        initPresentationControls();
        
        // Keyboard navigation
        document.addEventListener('keydown', handleKeyboard);
        
        // Touch/swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartedOnFooter = false;
        let touchStartedOnGlobalStructure = false;
        
        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            // Verificar si el touch inició en el footer
            const footer = document.querySelector('.slide-dots-container');
            if (footer && footer.contains(e.target)) {
                touchStartedOnFooter = true;
            } else {
                touchStartedOnFooter = false;
            }
            // Verificar si el touch inició en global-structure (slide 22)
            const globalStructure = document.querySelector('.global-structure');
            if (globalStructure && globalStructure.contains(e.target)) {
                touchStartedOnGlobalStructure = true;
            } else {
                touchStartedOnGlobalStructure = false;
            }
        }, { passive: true });
        
        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            // Solo procesar swipe si el touch NO inició en el footer ni en global-structure
            if (!touchStartedOnFooter && !touchStartedOnGlobalStructure) {
                handleSwipe();
            }
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }
        
        // Inicializar observadores y efectos
        setTimeout(() => {
            initSlideObserver();
            initHoverEffects();
            
            // Animar slide inicial si es el 1
            const activeSlide = document.querySelector('.slide.active');
            if (activeSlide && activeSlide.dataset.slide === '1') {
                // Animación del logo
                const logo = document.querySelector('.logo-icon');
                if (logo) {
                    logo.style.transform = 'scale(0)';
                    setTimeout(() => {
                        logo.style.transition = 'transform 0.5s ease';
                        logo.style.transform = 'scale(1)';
                    }, 200);
                }
            }
        }, 100);
    }

    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exponer funciones globales para debug
    window.miztonPresentation = {
        animateModelDiagram,
        animatePoolLiquid,
        animateTimeline,
        animateProjectCards,
        animateBenefits,
        animateVisionGrid,
        animateVestingLevels,
        animateStats,
        animateGlobalNet,
        animatePersonalNet
    };

})();

// =========================================================================
// DISCLAIMER PROGRESIVO
// =========================================================================

let currentDisclaimerStep = 1;
const totalDisclaimerSteps = 7;

function nextDisclaimerStep() {
    const currentStep = document.querySelector(`.disclaimer-step[data-step="${currentDisclaimerStep}"]`);
    const nextStepNum = currentDisclaimerStep + 1;
    const nextStep = document.querySelector(`.disclaimer-step[data-step="${nextStepNum}"]`);
    
    if (nextStep) {
        currentDisclaimerStep = nextStepNum;
        nextStep.classList.remove('hidden');
        
        // Scroll al nuevo paso
        nextStep.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Actualizar texto del botón en el último paso
        if (currentDisclaimerStep === totalDisclaimerSteps) {
            const btn = document.querySelector('.disclaimer-next-btn');
            if (btn) {
                btn.textContent = 'Continuar a la Presentación';
                btn.onclick = function() {
                    goToSlide(4); // Ir al slide siguiente (Narrativa RWA)
                };
            }
        }
    }
}

// =========================================================================
// ELEMENTOS CLICKEABLES - REVEAL
// =========================================================================

function initClickableElements() {
    // Todos los elementos con clase .blur-reveal
    document.querySelectorAll('.blur-reveal').forEach(element => {
        element.addEventListener('click', function() {
            this.classList.add('revealed');
        });
    });
    
    // Fast Active Info list items (slide 10) - usa estilos diferentes
    document.querySelectorAll('.fast-active-info li.clickable').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.add('revealed');
        });
    });
    
    // Timeline items (slide 12)
    document.querySelectorAll('.timeline-item.clickable').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.add('revealed');
        });
    });
}

// Función separada para inicializar flechas de proyectos
function initProjectArrows() {
    // Project arrows with token images
    document.querySelectorAll('.project-arrow').forEach(arrow => {
        arrow.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que el clic se propague al card
            const targetId = this.dataset.target;
            const img = document.getElementById(targetId);
            if (img) {
                img.classList.add('revealed');
            }
        });
    });
}

// =========================================================================
// CSS INJECTADO DINÁMICAMENTE
// =========================================================================

// Agregar estilos de ripple si no existen
if (!document.getElementById('ripple-styles')) {
    const style = document.createElement('style');
    style.id = 'ripple-styles';
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Inicializar elementos clickeables cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initClickableElements();
    initDisclaimerAutoProgress();
});

// =========================================================================
// DISCLAIMER AUTO-PROGRESIVO CON AUDIO
// =========================================================================

// Configuración de tiempos para cada step (en milisegundos desde el inicio del audio)
const DISCLAIMER_TIMINGS = {
    1: 0,           // Inicio del audio
    2: 12166,       // 12s 166ms
    3: 22750,       // 22s 750ms
    4: 26375,       // 26s 375ms
    5: 32208,       // 32s 208ms
    6: 39750,       // 39s 750ms
    7: 47625        // 47s 625ms
};

// Variable global para controlar el modo (auto o manual)
let disclaimerAutoMode = true;
let disclaimerTimers = [];

function initDisclaimerAutoProgress() {
    const audio3 = document.getElementById('audio-slide-3');
    if (!audio3) return;
    
    // Escuchar cuando el audio comienza a reproducirse
    audio3.addEventListener('play', function() {
        if (!disclaimerAutoMode) return; // No ejecutar si está en modo manual
        
        // Limpiar timers anteriores si existen
        clearDisclaimerTimers();
        
        // Mostrar el primer step inmediatamente
        showDisclaimerStep(1);
        
        // Programar la aparición de los demás steps
        for (let step = 2; step <= 7; step++) {
            const delay = DISCLAIMER_TIMINGS[step];
            const timer = setTimeout(() => {
                if (disclaimerAutoMode) {
                    showDisclaimerStep(step);
                }
            }, delay);
            disclaimerTimers.push(timer);
        }
    });
    
    // Limpiar timers si el audio se pausa o termina
    audio3.addEventListener('pause', clearDisclaimerTimers);
    audio3.addEventListener('ended', clearDisclaimerTimers);
}

function showDisclaimerStep(stepNumber) {
    const step = document.querySelector(`.disclaimer-step[data-step="${stepNumber}"]`);
    if (step && step.classList.contains('hidden')) {
        step.classList.remove('hidden');
        step.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Actualizar el contador global de step actual
        currentDisclaimerStep = stepNumber;
        
        // Si es el último step, actualizar el botón
        if (stepNumber === 7) {
            const btn = document.querySelector('.disclaimer-next-btn');
            if (btn) {
                btn.textContent = 'Continuar a la Presentación';
            }
        }
    }
}

function clearDisclaimerTimers() {
    disclaimerTimers.forEach(timer => clearTimeout(timer));
    disclaimerTimers = [];
}

// Función para cambiar entre modo automático y manual
// (para uso futuro cuando se implemente la selección de usuario)
function setDisclaimerMode(autoMode) {
    disclaimerAutoMode = autoMode;
    if (!autoMode) {
        clearDisclaimerTimers();
    }
}

// ═══════════════════════════════════════════════════════════════════
// VIDEO SLIDES - CONTROL DE REPRODUCCIÓN Y AUDIO SECUENCIAL
// ═══════════════════════════════════════════════════════════════════

// Inicializar controladores de video cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initVideoControllers();
});

function initVideoControllers() {
    Object.keys(VIDEO_SLIDES).forEach(slideNum => {
        const config = VIDEO_SLIDES[slideNum];
        const video = document.getElementById(config.videoId);
        const audio = document.getElementById(config.audioId);
        // Overlay específico por slide (solo slide 6 tiene overlay de mute)
        const muteOverlay = document.getElementById(`video-mute-overlay`);
        
        if (!video) return;
        
        // Función para actualizar estado del overlay de mute
        function updateMuteOverlay() {
            if (muteOverlay && slideNum === '6') {
                if (video.muted) {
                    muteOverlay.style.display = 'flex';
                    muteOverlay.querySelector('.mute-icon').textContent = '🔇';
                } else {
                    muteOverlay.style.display = 'none';
                }
            }
        }
        
        // Evento: Video terminó
        video.addEventListener('ended', function() {
            // Habilitar controles nativos del video al finalizar
            video.setAttribute('controls', 'true');
            
            // Reproducir audio si existe (después del video) - SOLO en modo auto
            if (presentationState.autoPlayMedia && audio) {
                audio.play().catch(e => {
                    console.log('Audio autoplay blocked:', e);
                });
                
                // Auto-scroll al caption cuando inicia el audio (solo vertical, no horizontal)
                const caption = document.querySelector('.slide[data-slide="' + slideNum + '"] .video-caption');
                if (caption) {
                    setTimeout(() => {
                        caption.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                    }, 300);
                }
            }
        });
        
        // Al hacer play en el video, pausar el audio
        video.addEventListener('play', function() {
            if (audio && !audio.paused) {
                audio.pause();
            }
        });
        
        // Click en video: quitar silencio (el usuario interactuó)
        video.addEventListener('click', function() {
            video.muted = false;
            updateMuteOverlay();
        });
        
        // Click en overlay de mute: quitar silencio y reproducir (slide 6)
        if (muteOverlay) {
            muteOverlay.addEventListener('click', function(e) {
                e.stopPropagation();
                video.muted = false;
                // Si el video está pausado, iniciar reproducción (para slide 6)
                if (video.paused) {
                    video.play().then(() => {
                        updateMuteOverlay();
                    }).catch(err => {
                        console.log('Video play error:', err);
                        // Si aún falla, mostrar mensaje
                        muteOverlay.querySelector('.mute-text').textContent = '🔒 Click para reproducir';
                    });
                } else {
                    video.play().catch(e => console.log('Video play error:', e));
                    updateMuteOverlay();
                }
            });
        }
        
        // Detectar cambios en el estado de mute del video
        video.addEventListener('volumechange', function() {
            updateMuteOverlay();
        });
        
        // Inicializar estado del overlay
        updateMuteOverlay();
        
        // Mostrar overlay después de un breve delay si sigue silenciado
        setTimeout(() => {
            updateMuteOverlay();
        }, 1000);
    });
}

// Reproducir video de nuevo (usado por controles nativos)
function replayVideo(slideNum) {
    const config = VIDEO_SLIDES[slideNum];
    if (!config) return;
    
    const video = document.getElementById(config.videoId);
    const audio = document.getElementById(config.audioId);
    
    if (video) {
        video.currentTime = 0;
        video.removeAttribute('controls');
        video.play();
        
        // Pausar audio si estaba reproduciéndose
        if (audio) {
            audio.pause();
            audio.currentTime = 0;
        }
    }
}

// Continuar al siguiente slide
function continueToNext(slideNum) {
    const nextSlideNum = parseInt(slideNum) + 1;
    goToSlide(nextSlideNum);
}

// ═══════════════════════════════════════════════════════════════════
// SLIDE 7 - SECUENCIA ESPECIAL: Audio1 -> Video Aleatorio -> Audio2
// ═══════════════════════════════════════════════════════════════════

function initSlide7Sequence(config) {
    const audioIntro = document.getElementById(config.audioIntroId);
    const audioOutro = document.getElementById(config.audioOutroId);
    const video = document.getElementById(config.videoId);
    const videoContainer = document.getElementById('video-7-container');
    const loadingOverlay = document.getElementById('video-7-loading');
    const loadRandomBtn = document.getElementById('load-random-video-btn');
    
    if (!video) return;
    
    // Configurar botón de cargar video secuencial (siempre disponible)
    if (loadRandomBtn) {
        loadRandomBtn.onclick = function() {
            loadRandomVideoForSlide7(config, true, false); // autoplay=true, isInitial=false (secuencial)
        };
    }
    
    // MODO MANUAL: autoPlayMedia desactivado
    if (!presentationState.autoPlayMedia) {
        // Ocultar loading overlay
        if (loadingOverlay) loadingOverlay.style.display = 'none';
        // Mostrar video con controles nativos
        video.style.display = 'block';
        video.setAttribute('controls', 'true');
        // Mostrar botón de cargar otro video
        if (loadRandomBtn) loadRandomBtn.style.display = 'inline-block';
        // Cargar un video aleatorio inicial (isInitial=true)
        loadRandomVideoForSlide7(config, false, true);
        return;
    }
    
    // MODO AUTO: comportamiento original
    if (!audioIntro) return;
    
    // Ocultar botón en modo auto
    if (loadRandomBtn) loadRandomBtn.style.display = 'none';
    
    // Cargar video aleatorio inicial (modo auto)
    loadRandomVideoForSlide7(config, false, true);
    
    // Reproducir audio intro primero
    audioIntro.play().catch(e => {
        console.log('Audio intro autoplay blocked:', e);
    });
    
    // Cuando termine el audio intro, mostrar y reproducir video
    audioIntro.addEventListener('ended', function onAudioIntroEnded() {
        audioIntro.removeEventListener('ended', onAudioIntroEnded);
        
        // Ocultar overlay de carga, mostrar video
        if (loadingOverlay) loadingOverlay.style.display = 'none';
        video.style.display = 'block';
        
        // Configurar evento para cuando termine el video
        video.addEventListener('ended', function onVideoEnded() {
            video.removeEventListener('ended', onVideoEnded);
            
            // Habilitar controles nativos
            video.setAttribute('controls', 'true');
            
            // Reproducir audio outro
            if (audioOutro) {
                audioOutro.play().catch(e => {
                    console.log('Audio outro autoplay blocked:', e);
                });
                
                // Auto-scroll al caption (solo vertical, no horizontal)
                const caption = document.querySelector('.slide[data-slide="7"] .video-caption');
                if (caption) {
                    setTimeout(() => {
                        caption.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
                    }, 300);
                }
            }
        });
        
        // Iniciar video
        video.play().catch(e => console.log('Video autoplay blocked:', e));
    });
}

// Variable global para índice secuencial de videos del slide 7
let slide7VideoIndex = 0;

// Función para cargar video en slide 7: inicial aleatorio, luego secuencial
// isInitial=true: primera carga (aleatoria), isInitial=false: cargas siguientes (secuencial)
function loadRandomVideoForSlide7(config, autoplay = true, isInitial = false) {
    const video = document.getElementById(config.videoId);
    if (!video) return;
    
    const timestamp = Date.now();
    
    if (isInitial) {
        // Primera carga: aleatoria (1 a newsVideosCount)
        slide7VideoIndex = Math.floor(Math.random() * config.newsVideosCount) + 1;
        console.log('Slide 7 - Carga inicial ALEATORIA:', slide7VideoIndex);
    } else {
        // Cargas siguientes: secuencial
        slide7VideoIndex = (slide7VideoIndex % config.newsVideosCount) + 1;
        console.log('Slide 7 - Carga SECUENCIAL:', slide7VideoIndex);
    }
    
    const videoFile = String(slide7VideoIndex).padStart(2, '0') + '.mp4';
    const videoPath = config.newsVideosPath + videoFile + '?v=' + timestamp;
    
    console.log('Slide 7 - Video path:', videoPath);
    
    const videoSource = video.querySelector('source');
    if (videoSource) {
        videoSource.src = videoPath;
        video.load();
    }
    
    if (autoplay) {
        video.play().catch(e => console.log('Video play blocked:', e));
    }
}

// ═══════════════════════════════════════════════════════════════════
// SLIDE 22: SCROLL AL NIVEL 9 ("...") A LOS 38 SEGUNDOS
// ═══════════════════════════════════════════════════════════════════

function initSlide22Scroll() {
    const timer = setTimeout(() => {
        if (currentSlide !== 22) return;
        
        const slide = document.querySelector('.slide[data-slide="22"]');
        const target = document.querySelector('.slide[data-slide="22"] [data-delay="46500"]');
        
        if (slide && target) {
            const slideRect = slide.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
            const relativeTop = targetRect.top - slideRect.top + slide.scrollTop;
            const scrollTarget = relativeTop - (slide.clientHeight / 2) + (target.clientHeight / 2);
            slide.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'smooth' });
        }
    }, 38000);
    
    activeRevealTimers.push(timer);
}

// ═══════════════════════════════════════════════════════════════════
// SLIDE 22: ANIMACIÓN AL REPRODUCIR AUDIO (modo AUTO)
// ═══════════════════════════════════════════════════════════════════

function initSlide22AudioAnimation() {
    const audio = document.getElementById('audio-slide-22');
    if (!audio) return;
    
    // Escuchar cuando el audio comienza a reproducirse para ejecutar animación
    audio.addEventListener('play', function onPlay() {
        // Solo ejecutar si estamos en slide 22
        if (currentSlide === 22 && window.miztonPresentation) {
            window.miztonPresentation.animateGlobalNet();
            window.miztonPresentation.animateStats();
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// SCROLL PROGRESIVO PARA SLIDES CON data-auto-scroll-duration
// ═══════════════════════════════════════════════════════════════════

function initProgressiveScroll(slide, duration) {
    // Esperar un momento para que el contenido se renderice
    const startDelay = 1000;
    
    const timer = setTimeout(() => {
        const maxScroll = slide.scrollHeight - slide.clientHeight;
        if (maxScroll <= 0) return;
        
        const startTime = performance.now();
        const startScroll = slide.scrollTop;
        
        function animateScroll(currentTime) {
            if (currentSlide !== parseInt(slide.dataset.slide)) return;
            
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease in-out para un movimiento suave
            const easeProgress = progress < 0.5
                ? 2 * progress * progress
                : 1 - Math.pow(-2 * progress + 2, 2) / 2;
            
            slide.scrollTop = startScroll + (maxScroll * easeProgress);
            
            if (progress < 1) {
                progressiveScrollAnimation = requestAnimationFrame(animateScroll);
            }
        }
        
        progressiveScrollAnimation = requestAnimationFrame(animateScroll);
    }, startDelay);
    
    activeRevealTimers.push(timer);
}

// ═══════════════════════════════════════════════════════════════════
// SLIDE 23: DUAL AUDIO (audio 1 → reveal → audio 2 → reveal)
// ═══════════════════════════════════════════════════════════════════

function initSlide23DualAudio() {
    const audio1 = document.getElementById('audio-slide-23');
    const audio2 = document.getElementById('audio-slide-23-2');
    const ctaBox = document.querySelector('.slide[data-slide="23"] [data-reveal-time-after-audio]');
    
    if (!audio1 || !audio2 || !ctaBox) return;
    
    // Ocultar el cta-box inicialmente
    ctaBox.style.opacity = '0.3';
    ctaBox.style.filter = 'blur(4px)';
    ctaBox.style.transform = 'scale(0.95)';
    ctaBox.style.transition = 'all 0.6s ease';
    const splash = ctaBox.querySelector('.vision-splash');
    if (splash) {
        splash.style.opacity = '0';
        splash.style.transition = 'opacity 0.3s ease';
    }
    
    // Cuando termine audio 1, reproducir audio 2
    function onAudio1Ended() {
        audio1.removeEventListener('ended', onAudio1Ended);
        if (currentSlide !== 23) return;
        
        audio2.play().catch(e => console.log('Audio 23-2 blocked:', e));
        
        // Revelar cta-box 900ms después de iniciar audio 2
        const revealDelay = parseInt(ctaBox.getAttribute('data-reveal-time-after-audio')) || 900;
        const timer = setTimeout(() => {
            if (currentSlide !== 23) return;
            
            ctaBox.style.opacity = '1';
            ctaBox.style.filter = 'blur(0)';
            ctaBox.style.transform = 'scale(1)';
            if (splash) splash.style.opacity = '0';
            
            // Auto-scroll
            const slide = document.querySelector('.slide[data-slide="23"]');
            if (slide) {
                const elRect = ctaBox.getBoundingClientRect();
                const slideRect = slide.getBoundingClientRect();
                const relativeTop = elRect.top - slideRect.top + slide.scrollTop;
                const scrollTarget = relativeTop - (slide.clientHeight / 2) + (ctaBox.clientHeight / 2);
                slide.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'smooth' });
            }
        }, revealDelay);
        activeRevealTimers.push(timer);
    }
    
    audio1.addEventListener('ended', onAudio1Ended);
}

// ═══════════════════════════════════════════════════════════════════
// GENÉRICO: SECUENCIA DE REVELADO CON TIMING PARA CUALQUIER SLIDE
// ═══════════════════════════════════════════════════════════════════

function initSlideRevealSequence(slideNumber) {
    // Limpiar timers anteriores
    clearRevealTimers();
    
    // El elemento .slide es el contenedor scrollable (tiene overflow-y: auto)
    const slide = document.querySelector(`.slide[data-slide="${slideNumber}"]`);
    if (!slide) return;
    
    const revealElements = slide.querySelectorAll('[data-reveal-time]');
    if (revealElements.length === 0) return;
    
    // Ocultar todos los elementos inicialmente
    revealElements.forEach(el => {
        el.style.opacity = '0.3';
        el.style.filter = 'blur(4px)';
        el.style.transform = 'scale(0.95)';
        el.style.transition = 'all 0.6s ease';
        el.classList.remove('revealed');
        
        // Ocultar el splash si existe
        const splash = el.querySelector('.vision-splash');
        if (splash) {
            splash.style.opacity = '0';
            splash.style.transition = 'opacity 0.3s ease';
        }
    });
    
    // Configurar timers para revelar cada elemento según su tiempo
    revealElements.forEach(el => {
        const revealTime = parseInt(el.getAttribute('data-reveal-time'));
        
        const timer = setTimeout(() => {
            // Guard: solo ejecutar si seguimos en el mismo slide
            if (currentSlide !== slideNumber) return;
            
            // Revelar elemento con animación
            el.style.opacity = '1';
            el.style.filter = 'blur(0)';
            el.style.transform = 'scale(1)';
            el.classList.add('revealed');
            
            // Mantener el splash oculto
            const splash = el.querySelector('.vision-splash');
            if (splash) {
                splash.style.opacity = '0';
            }
            
            // Auto-scroll vertical: usar el .slide (que tiene overflow-y: auto)
            // NO usar scrollIntoView (causa scroll horizontal en el presentation-container)
            const elRect = el.getBoundingClientRect();
            const slideRect = slide.getBoundingClientRect();
            const relativeTop = elRect.top - slideRect.top + slide.scrollTop;
            const scrollTarget = relativeTop - (slide.clientHeight / 2) + (el.clientHeight / 2);
            slide.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'smooth' });
        }, revealTime);
        
        activeRevealTimers.push(timer);
    });
}
